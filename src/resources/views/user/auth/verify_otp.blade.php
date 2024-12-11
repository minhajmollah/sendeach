@extends('layouts.frontend')
@section('content')
    <div class="col-12 col-md-12 col-lg-6 col-xl-6 px-0">
        <div class="login-left-section d-flex align-items-center justify-content-center">
            <div class="form-container">
                <div class="mb-3">
                    <h2>{{ translate('Verify OTP code') }}</h2>
                </div>
                <div class="alert alert-info">Please keep in mind that sometime the OTP message take upwords of two
                    minutes
                    to be sent from server.
                </div>
                <form action="{{ $route }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="my-3">
                        <div class="d-flex align-items-center border-bottom">
                            <i class="las la-lock fs-3 text-primary"></i>
                            <input type="text" name="code" value="{{ old('code') }}"
                                   placeholder="{{ translate('Enter your OTP code') }}"
                                   class="border-0 w-100 p-2"
                                   id="exampleInputEmail1" pattern="[0-9]{6}" minlength="4" maxlength="6" required/>
                        </div>
                        <label class="text-muted small">Please Check your Whatsapp or Email (Including Spam Folder) for
                            OTP</label>

                    </div>
                    <div class="my-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                            <label class="form-check-label" for="remember">
                                Remember this device
                            </label>
                        </div>
                    </div>

                    <x-no-captcha route="login.verify_otp"></x-no-captcha>

                    <button type="submit" class="shadow btn btn--info w-100 mt-2 text-light">
                        {{ translate('Validate OTP') }}
                    </button>
                </form>
                <p class="text-center mt-3">
                    {{ translate('Didn\'t recieve an OTP?') }} <a
                        href="{{ route('resend_otp', ['token' => $token, 'redirect_route' => $resend_route]) }}"
                        id="sendAgainButton"
                        data-remaining-time="{{ now()->diffInSeconds(\Carbon\Carbon::parse($decoded_token['otp_time'])->addMinutes(5), false) }}">{{ translate('Resend In: 05:00') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const route = jQuery('#sendAgainButton').attr('href');
        const enable_button = (interval = null) => {
            if (interval) {
                clearInterval(interval);
            }
            jQuery('#sendAgainButton').attr('href', route);
            jQuery('#sendAgainButton').text("Click here to resend!");
        }

        function str_pad_left(string, pad, length) {
            return (new Array(length + 1).join(pad) + string).slice(-length);
        }

        jQuery(document).ready(() => {
            jQuery('#sendAgainButton').attr('href', 'javascript: void(0);');
            let remaining_time = Number(jQuery('#sendAgainButton').attr('data-remaining-time'));
            if (remaining_time > 0) {
                let btnInterval = setInterval(() => {
                    if (remaining_time > 0) {
                        const minutes = Math.floor(remaining_time / 60);
                        const seconds = remaining_time - minutes * 60;
                        const timeString = str_pad_left(minutes, '0', 2) + ':' + str_pad_left(seconds, '0', 2);
                        jQuery('#sendAgainButton').text("Resend In: " + timeString);
                    } else {
                        enable_button(btnInterval);
                    }
                    remaining_time -= 1;
                }, 1000);
            } else {
                enable_button();
            }
        });
    </script>
@endpush
