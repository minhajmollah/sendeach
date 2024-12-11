<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __($general->site_name) }} - {{ __(@$title) }}</title>
    <!-- Favicons -->
    <link href="/assets/landing_page/img/favicon-send.png" rel="icon" />
    <link href="/assets/landing_page/img/favicon-send.png" rel="apple-touch-icon" />
    <script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}?v={{ config('app.asset_version') }}"></script>
    {{-- <link rel="shortcut icon" href="{{showImage(filePath()['site_logo']['path'].'/site_favicon.png')}}" type="image/x-icon"> --}}
    <link rel="stylesheet"
        href="{{ asset('assets/global/css/line-awesome.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/css/bootstrap.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/style.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/responsive.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/toastr.css') }}?v={{ config('app.asset_version') }}">
    @stack('stylepush')
</head>

<body>
    <div class="login-page-container">
        <div class="container-fluid p-0">
            <div class="row responsive-shadow overflow-hidden">
                @yield('content')
                <div class="col-12 col-md-12 col-lg-6 col-xl-6 px-0">
                    <div
                        class="login-right-section responsive-padding bg-purple d-flex align-items-center justify-content-center">
                        <div>
                            <h1>@lang('Welcome to') {{ __($general->site_name) }}</h1>
                            <p>{{ @$general->frontend_section->sub_heading }}</p>
                            @if (count($users) > 5)
                                <div class="users">
                                    @foreach ($users as $user)
                                        <div class="user">
                                            <img src="{{ showImage('assets/images/user/profile/' . $user->image) }}"
                                                alt="{{ $user->name }}" class="w-100 h-100" />
                                        </div>
                                    @endforeach
                                    <i class="fas fa-arrow-right fs-1 ms-3 text-light"></i>
                                </div>
                                <span class="text-light">{{ @$general->frontend_section->heading }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(session()->has('popup_modal'))
        @php
            $popup_modal = json_decode(session()->get('popup_modal'), true);
        @endphp
        <div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-{{ $popup_modal['modal_class'] }}">
                        <h5 class="modal-title text-white" id="popupModalLabel">{{ ucfirst($popup_modal['title']) }}</h5>
                    </div>
                    <div class="modal-body">
                        {!! $popup_modal['body'] !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('popupModal')">Close</button>
                        @if(isset($popup_modal['btn']) && is_array($popup_modal['btn']))
                            <a href="{{ $popup_modal['btn']['url'] }}" class="btn btn-{{ $popup_modal['modal_class'] }}">{{ $popup_modal['btn']['text'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/global/js/all.min.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/global/js/toastr.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}?v={{ config('app.asset_version') }}"></script>
    @include('partials.notify')
    @stack('scripts')
    @stack('scriptpush')
    <script>
        function hideModal(id) {
            if(document.getElementById(id)){
                jQuery(`#${id}`).modal('hide');
            }
        }
        jQuery(document).ready(function(){
            if(document.getElementById('popupModal')){
                jQuery('#popupModal').modal('show');
            }
        })
    </script>

    {!! NoCaptcha::renderJs() !!}
</body>

</html>
