let sendeach_chat_popup, sendeach_chat_btn, sendeach_chats, sendeach_messages, sendeach_send_message_button;

function getSenderHtml(message, created_at) {
    return ` <div class="row ml-3 justify-content-end mb-4">
    <div class="d-flex col-10 align-items-end flex-column">
        <p class="small p-2 me-3 mb-1 text-white rounded-3 bg-primary sendeach-chat-message">${message}</p>
        <p class="small me-3 mb-3 rounded-3 text-muted d-flex justify-content-end">${created_at ?? ''}</p>
    </div>
    <div class="bg-success col-2 rounded-circle d-flex justify-content-center align-items-center"
        style="width: 45px; height: 45px;">
        <i class="bi text-white bi-person-fill" style="font-size: 30px"></i>
    </div>
</div>`;
}

function getReceiverHtml(message, created_at) {
    return `<div class="row mr-3 justify-content-start mb-4">
    <div class="bg-success col-2 rounded-circle d-flex justify-content-center align-items-center"
        style="width: 45px; height: 45px;">
        <i class="bi text-white bi-person-fill" alt="avatar 1" style="font-size: 30px"></i>
    </div>
    <div class="d-flex col-10 align-items-start flex-column">
        <p class="small  p-2 ms-3 mb-1 rounded-3 sendeach-chat-message" style="background-color: #f5f6f7;"
            style="width: min-content;">${message}</p>
        <p class="small ms-3 mb-3 rounded-3 text-muted">${created_at ?? ''}</p>
    </div>
</div>`;
}

async function unreadMessages() {
    let messages = await jQuery.ajax({
        url: "{{ route('web_chats.unreadMessages') }}",
        headers: {
            'Authorization': 'Bearer ' + "{{ request('access_token') }}"
        }
    })

    console.log(messages)

    if (messages.length) {
        sendeach_chat_popup.removeClass('d-none')
        renderMessages(messages.reverse())
    }
}

async function sendMessage() {
    sendeach_messages.focus()
    if (!sendeach_send_message_button.prop('disabled') && sendeach_messages.val()) {
        sendeach_send_message_button.prop('disabled', true)

        sendeach_messages.prop('disabled', true)

        sendeach_chats.append(getSenderHtml(sendeach_messages.val(), moment().fromNow()))

        jQuery.ajax({
            url: "{{ route('web_chats.send') }}",
            method: 'POST',
            data: {
                message: sendeach_messages.val()
            },
            headers: {
                'Authorization': 'Bearer ' + "{{ request('access_token') }}"
            },
            success: function (reply) {
                if (reply) {
                    renderMessages([reply])
                }

                sendeach_messages.val('');
                sendeach_messages.prop('disabled', false)
                sendeach_messages.focus()

                sendeach_send_message_button.prop('disabled', false)

                sendeach_chats.scrollTop(sendeach_chats.prop('scrollHeight') - sendeach_chats.prop('clientHeight'))

            }
            ,
            error: function (response) {
                console.log(response)

                sendeach_messages.val('');
                sendeach_messages.prop('disabled', false)
                sendeach_messages.focus()

                sendeach_send_message_button.prop('disabled', false)

                if (response.responseJSON.message) {
                    sendeach_chats.append(getReceiverHtml(response.responseJSON.message))
                }

                sendeach_messages.prop('disabled', false)
                sendeach_send_message_button.prop('disabled', false)

                sendeach_chats.scrollTop(sendeach_chats.prop('scrollHeight') - sendeach_chats.prop('clientHeight'))
            }
        })
    }

}

async function getMessages() {
    return jQuery.ajax({
        url: "{{ route('web_chats.index') }}",
        headers: {
            'Authorization': 'Bearer ' + "{{ request('access_token') }}"
        },
    });
}

function renderMessages(messages) {
    messages.forEach(function (message) {

        message.message = message.message.replace(/(\r\n|\r|\n)/g, '<br>');

        if (message.is_sender) {
            sendeach_chats.append(getSenderHtml(message.message, moment(message.created_at).fromNow()))
        } else {
            sendeach_chats.append(getReceiverHtml(message.message, moment(message.created_at).fromNow()))
        }
    })

    jQuery(".sendeach-chat-message").each(function (i) {
        jQuery(this).html(createTextLinks_(jQuery(this).html()))
    });

    sendeach_chats.scrollTop(sendeach_chats.prop('scrollHeight') - sendeach_chats.prop('clientHeight'))
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
