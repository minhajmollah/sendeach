@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="row col-12">
                    <div class="col-12">
                        <div class="alert alert-warning">To delete WhatsApp messages sent from the Web Gateway/Desktop
                            Gateway, you must
                            use the
                            <b class="fw-bold">Same WhatsApp account</b>
                            for both the <b class="fw-bold">Web gateway and the Desktop gateway</b>.
                        </div>

                        {{--        @if(auth()->user()->credit < 1)--}}
                        {{--            <div class="alert alert-warning">--}}
                        {{--                In order to utilize this feature, it is necessary to have credits in your wallet.--}}
                        {{--                Therefore, please purchase some credits.--}}
                        {{--                <a href="{{ route('user.credits.create') }}" target="_blank">Buy Credits</a>--}}
                        {{--                <div class="text-dark">It's important to note that your credits will not be deducted when using this--}}
                        {{--                    feature.--}}
                        {{--                </div>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                    </div>
                    <div class="col-xl-12 col">
                        <form action="{{ route('user.whatsapp.messages.search') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @method('DELETE')
                            <div class="card mb-2">
                                <div class="card-header">
                                    {{ translate('WhatsApp Messages Delete')}}
                                </div>
                                <div class="card-body">
                                    <p class="text-secondary pb-2">The "SendEach WhatsApp Messages Delete" feature
                                        enables you to
                                        search for specific keywords using the SendEach Web Gateway and delete the
                                        corresponding
                                        messages.</p>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            {{ translate('Whatsapp Account') }}
                                        </label>
                                        <div class="input-group input-group-merge">
                                            <select class="form-select" name="whatsapp_device" id="whatsappDevice">
                                                <option disabled selected>Select Whatsapp Device</option>
                                                @foreach ($webDevices as $device)
                                                    <option
                                                        value="{{ $device->id }}"
                                                        @if (old('whatsapp_device') == $device->id) selected @endif>
                                                        {{ $device->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="keywords"
                                            class="form-label">{{translate('Enter the keywords to be searched')}}
                                            <sup
                                                class="text--danger">*</sup></label>
                                        <br>
                                        <div class="form-input-variables">
                                            <div class="input-group mb-3">
                                                <input type="text" name="keywords"  value="{{ old('keywords') }}" id="keywords" class="form-control"
                                                       placeholder="Enter Keywords to be Searched and Deleted">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input"
                                               name="is_exact" type="checkbox"
                                               id="is_exact">
                                        <label class="form-check-label" for="toggleButton">Search Exact Message Content</label>
                                    </div>
                                    <div class="row">
                                        <button type="submit"
                                                class="btn col-6 m-auto btn-primary me-sm-3 me-1 float-end">{{ translate('Search')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
{{--                    <div class="col-xl-6">--}}
{{--                        <form action="{{ route('user.whatsapp.messages.auto_delete.toggle') }}"--}}
{{--                              id="toggle_auto_delete_form"--}}
{{--                              method="POST" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="card mb-2">--}}
{{--                                <div class="card-header">--}}
{{--                                    {{ translate('Enable Auto Delete')}}--}}
{{--                                </div>--}}
{{--                                <div class="card-body">--}}
{{--                                    <div class="mb-3">--}}
{{--                                        <label--}}
{{--                                            class="form-label">{{translate('Enable Auto WhatsApp Message Delete')}} <sup--}}
{{--                                                class="text--danger">*</sup></label>--}}
{{--                                        <p class="text-secondary py-2">--}}
{{--                                            This feature enables automatic deletion of any WhatsApp messages sent--}}
{{--                                            through your PC gateway, eliminating the need for manual deletion.--}}
{{--                                        </p>--}}
{{--                                        <br>--}}
{{--                                        <div class="form-check form-switch form-switch-lg">--}}
{{--                                            <input class="form-check-input"--}}
{{--                                                   onchange="$('#toggle_auto_delete_form').submit()"--}}
{{--                                                   name="is_enabled" type="checkbox"--}}
{{--                                                   @if(auth()->user()->auto_delete_whatsapp_pc_messages) checked @endif--}}
{{--                                                   id="is_enabled">--}}
{{--                                            <label class="form-check-label" for="toggleButton">Enable Now</label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
                </div>
                <div class="col-lg-12">
                    @if(session()->has('messages'))
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="card-title">Search Results</div>
                                <div class="">
                                    <button class="btn btn--danger" onclick="deleteMessages()">Delete All</button>
                                </div>
                            </div>
                            <div class="responsive-table">
                                <table class="m-0 text-center table--light">
                                    <thead>
                                    <tr>
                                        <th>{{ translate('#')}}</th>
                                        <th>{{ translate('Body')}}</th>
{{--                                        <th>{{ translate('From')}}</th>--}}
                                        <th>{{ translate('To')}}</th>
                                        <th>{{ translate('Sent At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="search-results">
                                    @forelse(session()->get('messages') ?: [] as $message)
                                        <tr class="table-light">
                                            <input type="hidden" class="message_id" name="message_id" value="{{ $message['id'] }}">
                                            <input type="hidden" class="chat_id" name="chat_id" value="{{ $message['to'] }}">
                                            <td data-label="{{ translate('#')}}">
                                                {{$loop->iteration}}
                                            </td>

                                            <td data-label="{{ translate('body')}}">
                                                {{$message['body'] ?? '-'}}
                                            </td>

{{--                                            <td data-label="{{ translate('From')}}">--}}
{{--                                                {{$message['from'] ?? '-'}}--}}
{{--                                            </td>--}}

                                            <td data-label="{{ translate('To')}}">
                                                {{explode('@', $message['to'])[0] ?? '-'}}
                                            </td>

                                            <td data-label="{{ translate('timestamp')}}">
                                                {{\Illuminate\Support\Arr::get($message, 'timestamp')?->toDateTimeString() ?: '-'}}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center"
                                                colspan="100%">{{ translate('No Data Found')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="m-3">
                                {{$keywords->appends(request()->all())->links()}}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- whatsapp delete modal --}}
    <div class="modal fade" id="whatsappDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('user.whatsapp.messages.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="messageIds" id="messageIds" value="">
                    <input type="hidden" name="chatIds" id="chatIds" value="">
                    <input type="hidden" name="whatsapp_device" id="whatsapp_device" value="{{ old('whatsapp_device') }}">
                    <div class="modal_body2">
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete these messages') }}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="bg--danger">{{ translate('Delete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scriptpush')

    <script>
        function deleteMessages()
        {
            let messageIds  = [];
            let chatIds  = [];

            $(".message_id").each(function (i, message){
                messageIds.push($(message).val())
            })

            $(".chat_id").each(function (i, message){
                chatIds.push($(message).val())
            })

            let modal = $("#whatsappDelete")

            modal.find("#messageIds").val(messageIds)
            modal.find("#chatIds").val(chatIds)

            modal.modal('show')
        }
    </script>

@endpush
