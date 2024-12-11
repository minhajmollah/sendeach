@pushonce('scriptpush')
    <script>
        // Load the JavaScript SDK asynchronously
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        window.fbAsyncInit = function () {
            // JavaScript SDK configuration and setup
            FB.init({
                appId: '{{ config('whatsapp.app_id') }}', // Facebook App ID
                cookie: true, // enable cookies
                xfbml: true, // parse social plugins on this page
                version: 'v16.0' //Graph API version
            });
        };


        let redirectForm = (url) => `<form action="${url}" id="redirectForm" method="POST">@csrf
        <input type="hidden" name="auth_response" id="auth_response"></input></form>`

        function login(config_id, redirectUri = `{{ route('user.facebook.login.page') }}`) {
            FB.login(function (response) {
                console.log(response)

                let authResponse = response.authResponse

                if (authResponse) {
                    $("body").append(redirectForm(redirectUri))
                    $("#redirectForm").find("#auth_response").val(JSON.stringify(authResponse))
                    $("#redirectForm").submit()
                }
            }, {
                config_id: config_id, // configuration ID goes here
                response_type: 'code'   // must be set to 'code' for SUAT,
            });
        }
    </script>
@endpushonce
