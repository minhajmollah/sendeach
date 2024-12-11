<div class="form-popup d-none" id="chat-popup">
    <div class="w-100 h-100">
        <div class="card w-100 h-100" id="chat2">
            <div class="card-header d-flex justify-content-between align-items-center p-3">
                <h5 class="mb-0">Chat</h5>
                <button type="button" onclick="chatPopup.addClass('d-none')" class="close btn btn--primary bg-white h1 rounded" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="card-body" id="web-chats" style="position: relative; overflow-y: scroll; height: 400px"></div>
            <div class="card-footer ">
                <div class="row gap-1">
                    <div class="col-10">
                        <input type="text" class="form-control" id="chat-message"
                               placeholder="Type message">
                    </div>
                    <button id="chat_send_message_button"
                            class="col-2 btn ms-1 p-2 bg-success rounded-circle text-muted  d-flex justify-content-center align-items-center"
                            onclick="sendMessage()" style="width: 32px; height: 32px;">
                        <i class="bi bi-send text-white"></i></button>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    #chat2 .form-control {
        border-color: transparent;
    }

    #chat2 .form-control:focus {
        border-color: transparent;
        box-shadow: inset 0px 0px 0px 1px transparent;
    }

    .divider:after,
    .divider:before {
        content: "";
        flex: 1;
        height: 1px;
        background: #eee;
    }

    /* The popup chat - hidden by default */
    .form-popup {
        position: fixed;
        bottom: 80px;
        right: 80px;
        z-index: 9;
        width: 400px;
    }

    @media only screen and (max-width: 768px) {
        .form-popup {
            position: fixed;
            height: 100%;
            width: 100%;
            bottom: 0px;
            right: 0px;
            z-index: 1000;
        }
    }

    @keyframes float {
        0% {
            box-shadow: 0 5px 15px 0px rgba(0, 0, 0, 0.6);
            transform: translatey(0px);
        }
        50% {
            box-shadow: 0 25px 15px 0px rgba(0, 0, 0, 0.2);
            transform: translatey(-20px);
        }
        100% {
            box-shadow: 0 5px 15px 0px rgba(0, 0, 0, 0.6);
            transform: translatey(0px);
        }
    }

    .floating-button {
        transform: translatey(0px);
        animation: float 6s ease-in-out infinite;
    }
</style>

<script>
    let chats = $("#web-chats")
    let chatPopup = $("#chat-popup");
    let message = $("#chat-message")


    $("#chatButton").on('click', async function () {
        chatPopup.toggleClass('d-none')

        if (!chats.html()) {
            let messages = await getMessages();

            console.log(messages)

            messages.data.reverse()

            renderMessages(messages.data);
        }

        // setInterval(unreadMessages, 10000)
    })

    message.on("keypress", function onEvent(event) {
        if (event.key === "Enter") {
            sendMessage()
        }
    });

    function getSenderHtml(message, created_at) {
        return `      <div class="row ml-3 justify-content-end mb-4">
                        <div class="d-flex col-10 align-items-end flex-column">
                            <p class="small p-2 me-3 mb-1 text-white rounded-3 bg-primary chat-message">${message}</p>
                            <p class="small me-3 mb-3 rounded-3 text-muted d-flex justify-content-end">${created_at ?? ''}</p>
                        </div>
                        <div class="bg-success col-2 rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;">
                        <i class="bi text-white bi-person-fill"
                           alt="avatar 1" style="font-size: 30px"></i>
                        </div>
                    </div>`;
    }

    function getReceiverHtml(message, created_at) {
        return `<div class="row mr-3 justify-content-start mb-4">
                        <div class="bg-success col-2 rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;">
                            <i class="bi text-white bi-person-fill"
                               alt="avatar 1" style="font-size: 30px"></i>
                            </div>
                        <div class="d-flex col-10 align-items-start flex-column">
                            <p class="small  p-2 ms-3 mb-1 rounded-3 chat-message" style="background-color: #f5f6f7;" style="width: min-content;">${message}</p>
                            <p class="small ms-3 mb-3 rounded-3 text-muted">${created_at ?? ''}</p>
                        </div>
                    </div>`;
    }

    async function unreadMessages() {
        let messages = await $.ajax({
            url: '{{ route('web_chats.unreadMessages') }}'
        })

        console.log(messages)

        if (messages.length) {
            chatPopup.removeClass('d-none')
            renderMessages(messages.reverse())
        }
    }

    async function sendMessage() {

        let btn = $("#chat_send_message_button");

        if (!btn.prop('disabled') && message.val()) {
            btn.prop('disabled', true)

            message.prop('disabled', true)

            chats.append(getSenderHtml(message.val(), moment().fromNow()))

            $.ajax({
                url: '{{ route('web_chats.send') }}',
                method: 'POST',
                data: {
                    message: message.val()
                },
                success: function (reply) {
                    if (reply) {
                        renderMessages([reply])
                    }

                    message.val('');
                    message.prop('disabled', false)

                    btn.prop('disabled', false)

                    chats.scrollTop(chats.prop('scrollHeight') - chats.prop('clientHeight'))

                },
                error: function (response) {
                    console.log(response)

                    if(response.responseJSON.message)
                    {
                        chats.append(getReceiverHtml(response.responseJSON.message))
                    }

                    message.prop('disabled', false)
                    btn.prop('disabled', false)

                    chats.scrollTop(chats.prop('scrollHeight') - chats.prop('clientHeight'))
                }
            })
        }

    }

    async function getMessages() {
        return await $.ajax({
            url: '{{ route('web_chats.index') }}'
        })
    }

    function renderMessages(messages) {
        messages.forEach(function (message) {

            if (message.is_sender) {
                chats.append(getSenderHtml(message.message, moment(message.created_at).fromNow()))
            } else {
                chats.append(getReceiverHtml(message.message, moment(message.created_at).fromNow()))
            }
        })

        $(".chat-message").each(function (i) {
            $(this).html(createTextLinks_($(this).html()))
        });

        chats.scrollTop(chats.prop('scrollHeight') - chats.prop('clientHeight'))
    }


    function createTextLinks_(text) {
        return (text || '').replace(/([^\S]|^)(((https?\:\/\/)|(www\.))(\S+))/gi, function (match, space, url) {
            var hyperlink = url;
            if (!hyperlink.match('^https?://')) {
                hyperlink = 'http://' + hyperlink;
            }
            console.log(url)

            return space + '<a href="' + hyperlink + '" target="_blank">' + url + '</a>';
        });
    }
</script>
