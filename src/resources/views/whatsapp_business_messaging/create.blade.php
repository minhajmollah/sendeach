@extends(auth('web')->id() ? 'user.layouts.app' : 'admin.layouts.app')

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
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row d-flex align--center rounded">
                <div class="col-xl-12">
                    <div class="col-xl">
                        <form action="{{ auth('web')->id() ? route('user.business.whatsapp.send') : route('admin.business.whatsapp.send') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="card mb-2">
                                <h6 class="card-header">{{ translate('Recipient & Sending Device') }}</h6>
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
                                                            'toNumbersFromFileInput' => translate('To Recipient Number From File Upload'),
                                                            'toNumberGroupInput' => translate('To Recipient Number From Group'),
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
                                                <select class="form-select" name="whatsapp_phone_number_id"
                                                        id="whatsapp_phone_number_id">
                                                    </option>
                                                    @foreach ($whatsappPhoneNumbers as $whatsappNumber)
                                                        <option value="{{ $whatsappNumber->whatsapp_phone_number_id }}"
                                                                @if (old('whatsapp_phone_number_id') == $whatsappNumber->whatsapp_phone_number_id) selected @endif>{{ $whatsappNumber->whatsapp_account->name }}
                                                            - {{ $whatsappNumber->display_phone_number.' ('.$whatsappNumber->whatsapp_business_id.')' }}</option>
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
                                            <label class="form-label">
                                                {{ translate('To Recipient Number From Group') }}
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
                                            {{--                                            <label class="form-label">--}}
                                            {{--                                                {{ translate('Write Message') }} <sup class="text-danger">*</sup>--}}
                                            {{--                                            </label>--}}
                                            {{--                                            <div class="input-group input-group-merge speech-to-text" id="messageBox">--}}
                                            {{--                                                <textarea class="form-control" name="message" id="message"--}}
                                            {{--                                                    placeholder="{{ translate('Enter SMS Content &  For Mention Name Use ') }}@php echo "{{ ". 'name' ." }}" @endphp"--}}
                                            {{--                                                    aria-describedby="text-to-speech-icon"></textarea>--}}
                                            {{--                                                <span class="input-group-text" id="text-to-speech-icon">--}}
                                            {{--                                                    <i class='fa fa-microphone pointer text-to-speech-toggle'></i>--}}
                                            {{--                                                </span>--}}
                                            {{--                                            </div>--}}
                                            <div class="input-group">
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                   class="btn btn-primary"
                                                   data-bs-target="#templatedata">{{ translate('Select Template') }}</a>
                                            </div>
                                            {{--                                            <div class="text-end message--word-count"></div>--}}
                                            {{--                                            <div class="mt-3">--}}
                                            {{--                                                <label class="form-label">--}}
                                            {{--                                                    {{ translate('Choosen File') }} <sup class="text-danger">*</sup>--}}
                                            {{--                                                </label>--}}
                                            {{--                                                <select name="" class="form-select" id="selectTypeChange">--}}
                                            {{--                                                    <option value="">{{ translate('Select One') }}</option>--}}
                                            {{--                                                    <option value="file">{{ translate('File') }}</option>--}}
                                            {{--                                                    <option value="image">{{ translate('Image') }}</option>--}}
                                            {{--                                                    <option value="audio">{{ translate('Audio') }}</option>--}}
                                            {{--                                                    <option value="video">{{ translate('Video') }}</option>--}}
                                            {{--                                                </select>--}}
                                            {{--                                            </div>--}}

                                            <div id="templateContent" class="p-3" style="display: none">
                                                <input type="hidden" name="template_id" id="template_id">
                                                <div class="row row-cols-3">
                                                    <div class="m-2 col">
                                                        <span class="fw-bold">Template ID: </span>
                                                        <span id="templateID" class="text-secondary">name</span>
                                                    </div>
                                                    <div class="m-2 col">
                                                        <span class="fw-bold">Name: </span>
                                                        <span id="templateName" class="text-secondary">name</span>
                                                    </div>
                                                    <div class="m-2 col">
                                                        <span class="fw-bold">Category: </span>
                                                        <span id="templateCategory" class="text-secondary">name</span>
                                                    </div>
                                                    <div class="m-2 col">
                                                        <span class="fw-bold">Language: </span>
                                                        <span id="templateLanguage" class="text-secondary">name</span>
                                                    </div>
                                                    <div class="m-2 col">
                                                        <span class="fw-bold">status: </span>
                                                        <span id="templateStatus" class="text-secondary">name</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="fw-bold mt-2">Components:</div>
                                                    <div class="row" id="templateComponents">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-2">
                                <h6 class="card-header">{{ translate('Message Send Options') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label for="schedule" class="form-label">{{ translate('Message') }} <sup
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
                            <button type="submit"
                                    class="btn btn-primary me-sm-3 me-1">{{ translate('Submit') }}</button>
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
                            <div class="card-title text-center text--light">{{ translate('SMS Template') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="template" class="form-label">{{ translate('Select Template') }} <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" name="template" id="template" required>
                                    <option value="" disabled="" selected="">{{ translate('Select One') }}
                                    </option>
                                    @foreach ($templates as $template)
                                        <option
                                            value="{{ $template->whatsapp_template_id }}">{{ $template->name.' ('.$template->whatsapp_business_id.')' }}</option>
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
					<input type="datetime-local" name="schedule_date" id="schedule_date" class="form-control" required="">`;
                    $('.scheduledate').append(html);
                } else {
                    $('.scheduledate').empty();
                }
            });

            $('select[name=template]').on('change', function () {
                let templateID = $(this).val();
                $('#templatedata').modal('toggle');

                $.ajax({
                    url: '{{ auth('web')->id() ? route('user.business.whatsapp.template.getTemplate', 'templateID') :
                                                    route('admin.business.whatsapp.template.getTemplate', 'templateID') }}'.replace('templateID', templateID),
                    dataType: "json",
                    type: "get",
                    async: true,
                    data: {},
                    success: function (data) {
                        console.log(data);
                        renderTemplate(data)
                    },
                    error: function (xhr, exception) {
                    }
                });

            });

            function renderTemplate(template) {

                let components = template['components']

                $("#templateID").text(template['id'])
                $("#template_id").val(template['whatsapp_template_id'])
                $("#templateName").text(template['name'])
                $("#templateLanguage").text(template['language'])
                $("#templateStatus").text(template['status'])
                $("#templateCategory").text(template['category'])

                $("#templateContent").show()

                let componentHtml = '';

                components.forEach((component) => {

                    if (component.type === 'BUTTONS') return;

                    let variablesHtml = '';
                    if (component.example) {
                        variablesHtml += `<span class="fw-bold">Variables: </span>`;

                        let parameters = [];

                        if (component.type == 'BODY') parameters = component.example.body_text[0];
                        else if (component.type == 'HEADER' && component.format == 'TEXT') parameters = component.example.header_text;

                        parameters.forEach((parameter, i) => {
                            variablesHtml += getInputVariableHTML(parameter, component.type);
                        });
                    }

                    componentHtml += `<div class="row"> <div class="row bg--light shadow-sm p-2 m-2">
                                                <div class="m-2 col">
                                                    <span class="fw-bold">Text: </span>
                                                    <span class="text-secondary">${component.text}</span>
                                                </div>
                                                <div class="m-2 col">
                                                    <span class="fw-bold">Type: </span>
                                                    <span class="text-secondary">${component.type}</span>
                                                </div>
                                       </div> <div class="row">${variablesHtml}</div> </div>`;


                });
                $("#templateComponents").html(componentHtml)

            }

            function getInputVariableHTML(value, type) {
                return `<div class="col-12 mb-3"> <input type="text" class="form-control" name="template_var_${type}[]" id="template_var_${type}[]" placeholder="${value}" required></div>`
            }

            var wordLength = {{ $general->whatsapp_word_count }};

            $(`textarea[name=message]`).on('keyup', function (event) {
                var credit = wordLength;
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
                $('#text-to-speech-icon').on('click', function () {
                    var messageBox = document.getElementById('messageBox');
                    messageBox.querySelector(".form-control").focus(), n.onspeechstart = function () {
                        e = !0
                    }, !1 === e && n.start(), n.onerror = function () {
                        e = !1
                    }, n.onresult = function (e) {
                        messageBox.querySelector(".form-control").value = e.results[0][0].transcript
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

            const inputTypes = ['toNumberInput', 'toNumbersFromFileInput'];
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
        })(jQuery);
    </script>
@endpush
