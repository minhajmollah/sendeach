(function ($) {


    $(document).ready(function () {
        function isEmpty(obj) {
            return Object.keys(obj).length === 0 && obj.constructor === Object;
        }

        var url = window.location.href.replace("http://", "").replace("https://", "");

        var domain = url;
        console.log(domain)
        var data;
        $.ajax({
            url: "{{ route('domain') }}",
            data: {domain: domain},
            async: false,
            success: function (response) {


                data = response;
                console.log(data);

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log("Error:", textStatus, errorThrown);
            }
        });
        if (!isEmpty(data)) {
            console.log(data)
            if (data.status == 4) {
                console.log('its not matched url')
                return;
            }
            const databaseEntry = data.domain_name; // Example from the database
            const requestUrl = url; // Example from the request

// Extracting the domain and paths separately
            const dbDomainParts = databaseEntry.split('/');
            const dbDomain = dbDomainParts[0];
            const requestDomainParts = requestUrl.split('/');
            const requestDomain = requestDomainParts[0];


            if (data.status == 1) {

                const databaseEntry = data.domain_name; // Example from the database
                const requestUrl = url; // Example from the request


                let matchFound = false;

                if (dbDomain === requestDomain) {
                    for (let part of dbDomainParts) {
// Check for parts ending with '*' or '/*'
                        if (part.endsWith('*')) {
                            const routeName = part.replace('*', ''); // Removing * to get the route name

// Check if the route name exists in the request URL
                            if (requestUrl.includes(routeName)) {
                                matchFound = true;
                                break; // We found a match, no need to continue checking
                            }
                        }
                    }
                }

                if (matchFound) {
                    console.log("found route group");
                    addIcon();
                } else {
// Else condition: Check if server domain name equals request URL
                    const strippedDatabaseDomain = databaseEntry.replace(/\/$/, ''); // Remove trailing slash
                    const strippedRequestDomain = requestUrl.replace(/\/$/, ''); // Remove trailing slash

                    if (strippedDatabaseDomain === strippedRequestDomain) {
                        console.log("found single route");
                        addIcon();
                    }
                }

            } else if (data.status == 2) {


                let matchFounds = false;

                if (dbDomain === requestDomain) {
                    for (let part of dbDomainParts) {
                // Check for parts ending with '*' or '/*'
                        if (part.endsWith('*')) {
                            const routeName = part.replace('*', ''); // Removing * to get the route name

                // Check if the route name exists in the request URL
                            if (requestUrl.includes(routeName)) {
                                matchFounds = true;
                                break; // We found a match, no need to continue checking
                            }
                        }
                    }
                }
                const strippedDatabaseDomains = databaseEntry.replace(/\/$/, ''); // Remove trailing slash
                const strippedRequestDomains = requestUrl.replace(/\/$/, '');
                let single = strippedDatabaseDomains === strippedRequestDomains;
                console.log(single)

                if (matchFounds || single) {

                } else {


                    addIcon();

                }
            }
        } else {
            addIcon();
        }


    })

    function addIcon() {
        $("head").append(`
<!-- Vendor CSS Files -->


<link href="{{ asset('/assets/landing_page/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />

<link href="{{ asset('/assets/landing_page/css/webbot.css') }}" rel="stylesheet" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
`)

        $("body").append(`
<div class=" form-popup d-none" id="sendeach-chat-popup">
    <div class="">
        <div class="card" id="chat2">
            <div class="card-header d-flex justify-content-between align-items-center p-3">
                <h5 class="mb-0">Chat</h5>
            </div>
            <div class="card-body" id="sendeach-chats" style="position: relative; overflow-y: scroll; height: 400px">
            </div>
            <div class="card-footer ">
                <div class="row gap-1">
                    <div class="col-10">
                        <input type="text" class="form-control" id="sendeach-chat-message"
                            placeholder="Type message">
                    </div>
                    <button id="chat_send_message_button"
                        class="col-2 btn_custom ms-1 p-2 bg-success rounded-circle text-muted  d-flex justify-content-center align-items-center"
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
        z-index: 999;
        width: 400px;
    }

    @media (max-width: 560px) {
        .form-popup {
            position: fixed;
            bottom: 80px;
            /* right: 80px; */

            left: 50%;
            right: 50%;
            transform: translateX(-50%);
            z-index: 999;

        }
    }

    @media (max-width: 460px) {
        .form-popup {
            position: fixed;
            bottom: 80px;


            left: 50%;
            right: 50%;
            transform: translateX(-50%);
            z-index: 999;
            width: 350px;
        }
    }
</style>
`);

        sendeach_chat_popup = $("#sendeach-chat-popup");
        sendeach_chat_btn = $("#sendeach-chats-button");
        sendeach_chats = sendeach_chat_popup.find("#sendeach-chats");
        sendeach_messages = sendeach_chat_popup.find("#sendeach-chat-message");
        sendeach_send_message_button = sendeach_chat_popup.find("#chat_send_message_button");


        sendeach_chat_btn.on('click', async function () {
            sendeach_chat_popup.toggleClass('d-none')

            if (!sendeach_chats.html()) {
                let messages = await getMessages();

                console.log(messages)

                messages.data.reverse()

                renderMessages(messages.data);
            }

// setInterval(unreadMessages, 10000)
        })

        sendeach_messages.on("keypress", function onEvent(event) {
            if (event.key === "Enter") {
                sendMessage()
            }
        });
    }


})(jQuery)
@include('partials.chat-js')
