@extends('admin.layouts.app')
@push('stylepush')
    <style type="text/css">
        .ck-editor__editable_inline {
            min-height: 100px;
        }

        .select2-container {
            min-width: 100%;
        }

        #select2-group-results {
            min-width: 100%;
        }

        .select2-container .select2-selection--single {
            height: auto;
            padding-top: .375rem;
            padding-bottom: .375rem;
        }
    </style>
@endpush
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="p-0 pb-2 container-fluid">
            <div class="rounded row d-flex align--center">
                <div class="col-xl-12">
                    <div class="col-xl">
                        <form action="{{ route('admin.email.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('Email Address collect in a different ways') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">
                                                {{ translate('Send to') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="recipent_type" id="recipentType">
                                                    <option value="" disabled="">{{ translate('Select One') }}
                                                    </option>
                                                    @php
                                                        $sendingTypes = [
                                                            'toEmailInput' => translate('To Recipient Emails'),
                                                            'toEmailGroupInput' => translate('To Recipient Email From Group'),
                                                            'toEmailsFromFileInput' => translate('To Recipient Email From File Upload'),
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
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label">
                                                {{ translate('Send Gateway') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select" name="mail_gateway" id="whatsappDevice">
                                                    @foreach ($mailGateways as $gateway)
                                                        <option value="{{ $gateway->id }}"
                                                            @if ($gateway->default_use == 1) selected
                                                    @elseif ($gateway->default_use == 2) selected @endif>
                                                            {{ $gateway->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2 col-12" id="toEmailInput">
                                            <label class="form-label">
                                                {{ translate('To Email') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select emailcollect" name="email[]" id="email"
                                                    multiple>
                                                    <option value="">{{ translate('Select One') }}</option>
                                                    @foreach ($emailContacts as $contact)
                                                        <option value="{{ $contact->email }}">{{ $contact->email }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2 col-12" id="toEmailGroupInput">
                                            <label class="form-label">
                                                {{ translate('To Email From Group') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <select class="form-select keywords" name="email_group_id[]" id="group"
                                                    multiple="multiple">
                                                    <option value="" disabled="">{{ translate('Select One') }}

                                                    </option>
                                                    @foreach ($emailGroups as $group)
                                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-text">
                                                {{ translate('Can be select single or multiple group') }}
                                            </div>
                                        </div>
                                        <div class="mb-2 col-12" id="toEmailsFromFileInput">
                                            <label class="form-label">
                                                {{ translate('To Email From File Upload') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control" type="file" name="file" id="file">
                                            </div>
                                            <div class="form-text">
                                                {{ translate('Supported files: csv, excel. Download all files from here: ') }}
                                                {{ translate('') }}
                                                <a href="{{ route('demo.email.file.downlode', 'csv') }}">{{ translate('csv') }},
                                                </a>
                                                <a
                                                    href="{{ route('demo.email.file.downlode', 'xlsx') }}">{{ translate('xlsx') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('Optional Information') }} <input type="checkbox"
                                        class=" checked_opt" name=""> <sup class="pointer"
                                        title="{{ translate('Show \'Sender Name and Reply To\' the recipients mail with these options') }}">
                                        <i class="fa fa-info-circle"></i></sup></h6>
                                <div class="card-body" id="optional_info">
                                    <div class="row">
                                        <div class="mb-2 col-md-6">
                                            <label class="form-label">
                                                {{ translate('Send From') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control"
                                                    placeholder="{{ translate('Sender Name (Optional)') }}" type="text"
                                                    name="from_name" id="from_name">
                                            </div>
                                        </div>
                                        <div class="mb-2 col-md-6">
                                            <label class="form-label">
                                                {{ translate('Reply To Email') }}
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control" type="email"
                                                    placeholder="{{ translate('Reply To Email (Optional)') }}"
                                                    name="reply_to_email" id="reply_to_email">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('Email Subject & Body') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ translate('Subject') }} <sup class="text-danger">*</sup>
                                            </label>
                                            <div class="input-group input-group-merge">
                                                <input type="text" name="subject" id="subject" class="form-control"
                                                    placeholder="{{ translate('Write email subject here') }}">
                                            </div>
                                        </div>

                                        <div class="md-12">
                                            <label class="form-label">
                                                {{ translate('Message Body') }} <sup class="text-danger">*</sup>
                                            </label>
                                            <div class="input-group">
                                                <textarea class="form-control" name="message" id="message" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 card">
                                <h6 class="card-header">{{ translate('Sending Options') }}</h6>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-4 col-md-6">
                                            <label for="schedule" class="form-label">{{ translate('Email') }} <sup
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
@endsection


@push('scriptpush')
    <script>
        function setScheduledDate() {
            $("#schedule_date_timezone").val((new Date($("#schedule_date").val())).toISOString())
        }

        (function($) {
            "use strict";
            $('.keywords').select2({
                tags: true,
                tokenSeparators: [',']
            });

            $('.emailcollect').select2({
                tags: true,
                tokenSeparators: [',']
            });

            if ($('.checked_opt').is(":checked")) {
                $("#optional_info").show(300);
            } else {
                $("#optional_info").hide(200);
            }
            $(".checked_opt").click(function() {
                if ($(this).is(":checked")) {
                    $("#optional_info").show(300);
                } else {
                    $("#optional_info").hide(200);
                }
            });

            $('input[type=radio][name=schedule]').on('change', function() {
                if (this.value == 2) {
                    const translate = "{{ translate('Schedule Date & Time') }}"
                    var html = `
	        		<label for="schedule_date" class="form-label">{{ translate('Schedule') }}<sup class="text-danger">*</sup></label>
	        		<input type="hidden" name="schedule_date" id="schedule_date_timezone">
					<input type="datetime-local" onchange="setScheduledDate()" id="schedule_date" class="form-control" required="">`;
                    $('.scheduledate').append(html);
                } else {
                    $('.scheduledate').empty();
                }
            });


            $(document).ready(function() {
                $('#message').summernote({
                    placeholder: '{{ translate('Write Here Email Content &  For Mention Name Use ') }}' +
                        '{' + '{name}' + "}",
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
            });

            const inputTypes = ['toEmailInput', 'toEmailGroupInput', 'toEmailsFromFileInput'];
            const recipentTypeHandler = function() {
                const selected = $('#recipentType').val();
                inputTypes.forEach(id => {
                    document.getElementById(id).classList.add('d-none');
                });
                if (selected == "toEmailGroupInput") {
                    $('#group').select2({
                        tags: true,
                        tokenSeparators: [',']
                    });
                }
                if (selected == "toEmailInput") {
                    $('.emailcollect').select2({
                        tags: true,
                        tokenSeparators: [','],
                        placeholder: "Enter emails seperated by commas"
                    });
                }

                document.getElementById(selected).classList.remove('d-none');
            };
            recipentTypeHandler();
            $(document).on('change', '#recipentType', recipentTypeHandler);
        })(jQuery);
    </script>
@endpush
