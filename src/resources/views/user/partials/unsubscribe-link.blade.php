<div id="unsubscribe-div" style="display: none;">
    <p class="py-2 text-dark">
        To include an unsubscribe link in your <b class="fw-bold">group send</b> message, just use the
        variable text <b class="fw-bold"> @{{unsubscribe}} </b> wherever you want it.
        This variable will be
        replaced with the actual unsubscribe link.
    </p>
    <div class="input-group">
        <input type="text" class="form-control"
               value="<a href='@{{unsubscribe}}'>Un Subscribe</a>" id="unsubscribeLink"
               aria-describedby="basic-addon2" readonly="">
        <div class="input-group-append pointer">
                                            <span class="input-group-text bg--success text--light" id="basic-addon2"
                                                  onclick="copyAccessToken('unsubscribeLink')">Copy UnSubscribe Link</span>
        </div>
    </div>
</div>

@push('scriptpush')
    <script>
        function copyAccessToken(elemID) {
            var copyText = document.getElementById(elemID);
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', "Copied Un Subscribe Link");
        }
    </script>
@endpush
