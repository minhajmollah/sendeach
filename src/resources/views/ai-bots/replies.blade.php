@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')

@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success row">
                        <div class="col-9">
                            <h6 class="fw-bold mb-1 p-0">Utilize
                                custom replies to conserve your OpenAI Tokens</h6>
                            Custom Replies are given priority over AI-generated replies. You have the option to import
                            custom replies from your past AI conversations.
                            <i> <b class="fw-bold">Note: </b> A minimum of 500 AI chats is required for importing into
                                custom replies.</i>
                        </div>
                        <div class="col-3 text-end">
                            <a href="{{ route($routePrefix.'.ai_bots.custom_replies.import', ['ai_bot_id' => request('ai_bot_id')]) }}"
                               class="btn btn--success text-dark fw-bold">Import From AI Conversations</a>
                            <div class="badge badge--success text-dark p-2 mt-2">Total Chats with AI
                                Bot: {{ $totalChats }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="alert alert-primary row">
                        <div class="col-10">Make use of the <i>"Connect to Human"</i> feature to temporarily pause the
                            AI conversation with your specific customer. Throughout this duration, the bot will not
                            furnish responses to any further inquiries in that session. Additionally, you can modify
                            particular keywords to function as a means to connect to a human.
                        </div>
                        <div class="col-2 text-end">
                            <button type="button" data-bs-target="#connect_to_human" data-bs-toggle="modal"
                                    class="btn btn--primary">Connect to Human
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 col">
                    <form action="{{ route($routePrefix.'.ai_bots.custom_replies.toggle_partial_match') }}"
                          id="keyword_match_type"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('Keyword Match Type')}}
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label
                                        class="form-label"
                                        for="keyword_match_type">{{translate('Set Keyword Match type')}} <sup
                                            class="text--danger">*</sup></label>
                                    <p class="text-secondary py-2">By default, the system is set to locate an exact
                                        keyword match. If you decide to activate partial matching, keep in mind that
                                        while it's faster, it might not ensure accuracy.
                                    </p>
                                    <br>
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input"
                                               onchange="$('#keyword_match_type').submit()"
                                               name="is_partial_match" type="checkbox"
                                               @if(Arr::get(@$aiBot->data, 'custom_replies.is_partial_match')) checked
                                               @endif
                                               id="keyword_match_type">
                                        <label class="form-check-label" for="toggleButton">Enable Partial Match</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-xl-12 col row">
                                    <form method="GET" class="col row">
                                        <label class="form-label" for="search">
                                            Search Messages
                                        </label>
                                        <div class="col-9">
                                            <input class="form-control" id="search" name="search"
                                                   value="{{ request('search') }}"
                                                   placeholder="Search Messages, Replies, Keywords">
                                        </div>
                                        <button type="submit" class="btn btn--primary text-right col-3">Search</button>
                                    </form>
                                    <div
                                        class="col-12 row justify-content-between align-items-center gap-2 m-0 mt-2 mb-2 p-0">
                                        <div class="d-flex col-lg-4 gap-2 col-sm-12 col-md-6 justify-content-start">
                                            <div class="">
                                                <button class="btn btn--danger delete"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Delete">{{translate('Delete')}}</button>
                                            </div>
                                            <div class="">
                                                <button class="btn btn--danger delete"
                                                        type="submit" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" data-delete-all="true"
                                                        title="Delete All">{{ translate('Delete All') }}</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="responsive-table">
{{--                            <div class="bg-white p-3 pb-1">--}}
{{--                                <h5 class="h5">Human Keywords</h5>--}}
{{--                                <p class="text-muted small">These keywords will be paused for a given duration, so that you can communicate with them effectively.</p>--}}

{{--                            </div>--}}
                                 <table class="m-0 table--light">
                                <thead>
                                <tr>
                                    <th class="d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-2 checkAll"
                                               type="checkbox"
                                               aria-label="Checkbox for following text input">{{ translate('#')}}
                                    </th>
                                    <th>{{ translate('Keywords')}}</th>
                                    <th>{{ translate('Reply')}}</th>
                                    <th>{{ translate('Pause Duration')}}</th>
                                    <th>{{ translate('Is Partial Match')}}</th>
                                    <th>{{ translate('Action')}}</th>
                                </tr>
                                </thead>
                                @forelse($replies as $reply)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td class="d-none d-md-flex align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="checkbox"
                                                   name="reply_id"
                                                   value="{{$reply->id}}"
                                                   aria-label="Checkbox for following text input">
                                            {{$loop->iteration}}
                                        </td>
                                        <td data-label="{{ translate('keywords')}}">
                                            <div class="text-truncate" style="max-width: 500px;">
                                                {{  $reply->keywords ?: '-' }}
                                            </div>
                                        </td>

                                        <td data-label="{{ translate('reply')}}">
                                            <div class="text-truncate" style="max-width: 500px;">
                                                {!! $reply->reply  !!}
                                            </div>
                                        </td>

                                        <td data-label="{{ translate('Connects To Human ?')}}">
                                            <div class="text-truncate" style="max-width: 500px;">
                                                {!! $reply->to_pause ? $reply->pause_duration.' Minutes': 'Instant' !!}
                                            </div>
                                        </td>

                                        <td data-label="{{ translate('Is Partial Match ?')}}">
                                            <div class="text-truncate" style="max-width: 500px;">
                                                {!! $reply->is_partial_match ? 'Yes': 'No' !!}
                                            </div>
                                        </td>

                                        <td data-label={{ translate('Action')}}>
                                            <a class="btn--primary text--light edit-message" data-bs-toggle="modal"
                                               data-bs-target="#edit_message" href="javascript:void(0)"
                                               data-id="{{ $reply->id }}"
                                               data-is-partial-match="{{ $reply->is_partial_match }}"
                                               data-pause-duration="{{ $reply->pause_duration }}"
                                               data-to-pause="{{ $reply->to_pause }}"
                                               data-message="{{ $reply->message }}"
                                               data-keywords="{{  $reply->keywords }}"
                                               data-reply="{{ $reply->reply }}"><i class="las la-pen"></i></a>
                                            <a href="{{ route($routePrefix.'.ai_bots.custom_replies.delete', ['message_id' => $reply->id]) }}"
                                               class="btn--danger text--light delete_reply"
                                            ><i class="las la-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center"
                                            colspan="100%">{{ translate('No Data Found')}}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{ $replies instanceof  \Illuminate\Pagination\LengthAwarePaginator ?  $replies->appends(request()->all())->links() : ''}}
                        </div>
                    </div>
                </div>

                <div class="col mt-4 d-flex gap-2">
                    <button class="btn btn--info" id="add-reply" data-bs-toggle="modal"
                            data-bs-target="#edit_message" type="button"><i
                            class="las la-plus-circle"></i> Add Reply
                    </button>
                    {{--                    <a href="{{ route($routePrefix.'.ai_bots.custom_replies.import', ['ai_bot_id' => request('ai_bot_id')]) }}"--}}
                    {{--                       class="btn btn--primary">Import from chats</a>--}}
                    {{--                    <button data-bs-target="#fetch_keywords" data-bs-toggle="modal" class="btn btn--primary">Fetch and--}}
                    {{--                        Update Keywords From OpenAI--}}
                    {{--                    </button>--}}
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="edit_message" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <form action="{{ route($routePrefix.'.ai_bots.custom_replies.update') }}"
              class="row align-items-center"
              method="POST">
            @method('PUT')
            @csrf
            <div class="modal-dialog" style="max-width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{ translate('Message') }}</h5>
                        <button type="button"
                                data-bs-dismiss="modal"
                                class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col col-12 row gap-4 m-0">
                            <input type="hidden" id="message_id" name="customs[0][id]"
                                   value="">
                            <div class="col-12">
                                <label class="form-label" for="keywords">Enter keywords seperated by <b class="fw-bold">`,`</b></label>
                                <textarea type="text" class="form-control length-indicator" data-max-length="1000"
                                          placeholder="Keywords" rows="5" id="keywords" name="customs[0][keywords]"
                                          required></textarea>
                            </div>

                            <div class="col-12">
                                <label for="reply">Reply</label>
                                <textarea type="text" class="form-control length-indicator" placeholder="Reply"
                                          name="customs[0][reply]" id="reply" rows="8" data-max-length="1000"
                                          required></textarea>
                            </div>

                            <div class="col-12">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="customs[0][is_partial_match]" id="is_partial_match">
                                    <label class="form-check-label" for="is_partial_match">
                                        Allow Partial Match
                                    </label>
                                    <small class="text-muted">By default, the system is set to locate an exact keyword
                                        match. Note: Exact Matched keywords are given more priority.</small>
                                </div>

                            </div>

                            <div class="col-12">

                                <div class="form-check">
                                    <input class="form-check-input" onchange="togglePauseMinutes()" type="checkbox"
                                           name="customs[0][to_pause]" id="to_pause">
                                    <label class="form-check-label" for="to_pause">
                                        Connect to Human
                                    </label>
                                    <small class="text-muted">Enabling this option will temporarily prevent the AI Bot
                                        from
                                        responding to this specific customer, allowing you to engage in direct
                                        communication
                                        with them during that period.</small>
                                </div>

                            </div>

                            <div class="col-12" id="pause_duration_div">
                                <label class="form-label" for="pause_duration">
                                    Pause Minutes
                                </label>
                                <input class="form-control" type="number" min="0" value=""
                                       name="customs[0][pause_duration]"
                                       id="pause_duration">

                                <small class="text-muted">Specify the duration for pausing the AI conversation with the
                                    customer.</small>
                            </div>

                            {{--                            <div class="col-12">--}}
                            {{--                                <label class="form-label" for="message">Sample User Message</label>--}}
                            {{--                                <textarea type="text" class="form-control" placeholder="Message"--}}
                            {{--                                          id="message" rows="5" name="customs[0][message]"></textarea>--}}
                            {{--                            </div>--}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary" type="submit">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>

        $("#pause_duration_div").hide()

        function togglePauseMinutes() {
            if ($("#to_pause").is(':checked')) {
                $("#pause_duration_div").show()
            } else {
                $("#pause_duration_div").hide()
            }
        }
    </script>

    <div class="modal fade" id="connect_to_human" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route($routePrefix.'.ai_bots.custom_replies.connect_to_human') }}">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal_body2">
                        <div class="row">
                            <div class="mb-4 col-12 text-start">
                                <label class="form-label" for="sender">Customer</label>
                                <select class="form-select" id="sender" name="sender" required>
                                    <option disabled>Select Your Customer</option>
                                    @foreach($conversations as $conversation)
                                        <option value="{{ $conversation->id }}">{{ $conversation->messageable?->name
                                                    ? $conversation->messageable?->name . ' (Whatsapp User)'
                                                    : $conversation->messageable?->psid . ' (Facebook User)' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-12 text-start">
                                <label class="form-label" for="duration">Pause Duration (In Minutes)</label>
                                <input class="form-control" type="number" min="1" id="duration"
                                       placeholder="Conversation Stop Duration in Minutes" name="duration" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                        <button type="submit" class="bg--primary">{{translate('Pause')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fetch_keywords" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route($routePrefix.'.ai_bots.custom_replies.fetch_keywords_from_ai') }}">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal_body2">
                        <div class="modal_text2 mt-3">
                            <h6>Note: Please be aware that utilizing OpenAI's keyword retrieval feature will deduct the
                                corresponding credits from your account. .</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                        <button type="submit" class="bg--primary">{{translate('Fetch')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route($routePrefix.'.ai_bots.custom_replies.delete') }}"
                      method="GET">
                    @csrf
                    <input type="hidden" name="message_id">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete these custom replies ?')}}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="bg--danger">{{ translate('Delete')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@pushonce('scriptpush')
    <script>

        let i = {{ count($replies ?? []) }};

        $(".edit-message").on('click', function () {
            let modal = $("#edit_message")
            console.log($(this).data())
            modal.find("#message_id").val($(this).data("id"))
            modal.find("#message").val($(this).data("message"))
            modal.find("#reply").val($(this).data("reply"))
            modal.find("#keywords").val($(this).data("keywords"))
            modal.find("#is_partial_match").prop('checked', $(this).data("isPartialMatch"))
            if ($(this).data("toPause")) {
                $("#pause_duration_div").show()
                modal.find("#to_pause").prop('checked', true);
                modal.find("#pause_duration").val($(this).data("pauseDuration"))
            } else {
                $("#pause_duration_div").hide()
                modal.find("#to_pause").prop('checked', false);
                modal.find("#pause_duration").val(0)
            }
        })

        $("#add-reply").on('click', function () {
            let modal = $("#edit_message")
            modal.find("#message_id").val('')
            modal.find("#message").val('')
            modal.find("#reply").val('')
            modal.find("#keywords").val('')
        })

        function deleteReply(e) {

            let elem = $(e);

            if (elem.data('id')) {
                let toDelete = $("#toDelete");
                let ids = toDelete.val() ? toDelete.val().split(',') : []
                ids.push(elem.data('id'))
                toDelete.val(ids)
            }

            elem.parent().parent().remove()
        }

        function addReply() {
            $("#replies").append(`
            <div class="col col-12 mt-4 row gap-2">
                                        <div class="col-6">
                                            <textarea type="text" class="form-control" placeholder="Message"
                                                   name="customs[${i}][message]" required></textarea>
                                        </div>
                                        <div class="col-5">
                                            <textarea type="text" class="form-control" placeholder="Reply" name="customs[${i}][reply]" required></textarea>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control" placeholder="Keywords" name="customs[${i}][keywords]">
                                        </div>
                                        <div class="col-1">
                                            <button onclick="deleteReply(this)" class="btn btn-outline-danger" type="button"><i
                                                    class="las fs-5 la-trash"></i></button>
                                        </div>
                                    </div>
            `)

            i++;
        }

        $("#ai_bot_id").on('change', function () {
            window.location.href = window.location.pathname + "?ai_bot_id=" + $("#ai_bot_id").val()
        })

        $('.checkAll').click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);

            let ids = [];
            $("input:checkbox[name=whatsappid]:checked").each(function () {
                ids.push($(this).val());
            });

            $("#gateway_id").val(ids.join(', '))
        });

        $('.delete').on('click', function () {
            let modal = $('#delete');

            let ids = [];
            $("input:checkbox[name=reply_id]:checked").each(function () {
                ids.push($(this).val());
            });

            let data = $(this).data('delete_id')
            data ? ids.push(data) : null

            if ($(this).data('delete-all')) {
                ids = 'delete-all'
            }

            modal.find('input[name=message_id]').val(ids);
            modal.modal('show');
        });
    </script>

@endpushonce

