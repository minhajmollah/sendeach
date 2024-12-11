<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __(@$general->site_name) }} - {{ __(@$title) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{ showImage(filePath()['site_logo']['path'] . '/site_favicon.png') }}"
        type="image/x-icon">
    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}?v={{ config('app.asset_version') }}"></script>

    <link rel="stylesheet"
        href="{{ asset('assets/global/css/bootstrap.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/global/css/line-awesome.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/css/select2.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/toastr.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/css/apexcharts.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/css/datepicker/datepicker.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/style.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/css/responsive.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/css/summernote-lite.min.css') }}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/dashboard/flag-icons/flag-icons.css') }}?v={{ config('app.asset_version') }}">
    @stack('style-include')
    @stack('stylepush')
    <script>
        const MODULES = @json(config('app.modules'));
    </script>
</head>

<body>

    @yield('content')
    @stack('cdn-push')


    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}?v={{ config('app.asset_version') }}"></script>


    <script src="{{ asset('assets/global/js/all.min.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/select2.min.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/global/js/toastr.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/chart.min.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/apexcharts.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/ckeditor.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/datepicker/datepicker.min.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script src="{{ asset('assets/dashboard/js/datepicker/datepicker.en.js') }}?v={{ config('app.asset_version') }}">
    </script>
    <script src="{{ asset('assets/dashboard/js/script.js') }}?v={{ config('app.asset_version') }}"></script>
    <script src="{{ asset('assets/dashboard/js/summernote-lite.min.js') }}?v={{ config('app.asset_version') }}"></script>

    <link href="https://cdn.datatables.net/v/bs5/dt-1.13.6/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.6/datatables.min.js"></script>

    @include('partials.notify')
    @stack('script-include')
    @stack('scriptpush')

    <script type="text/javascript">
        'use strict';

        function changeLang(val) {
            window.location.href = "{{ route('login') }}/language/change/" + val;
        }
        $(".active").focus();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(`.length-indicator`).on('keyup', (e) => calculateLength(e.currentTarget));

        function calculateLength(e) {
            let input = $(e);
            let worCountElem = input.siblings(".message--word-count")
            let character = input.val();
            let totalLength = input.data('max-length')
            let characterLeft = totalLength - character.length;
            let word = character.split(" ");
            let wordLength = character.length
            let status = 'primary';

            if (characterLeft < 0) {
                status = 'danger';
            }

            if (!worCountElem.length) {
                input.after(`<div class="text-end message--word-count"></div>`)
            }

            if (character.length > 0) {
                worCountElem.html(`
                	<span class="text--success character">${character.length} / ${totalLength} </span> {{ translate('Characters') }} |
					<span class="text--success word">${word.length}</span> {{ translate('Words') }} |
                    <span class="text--${status} word">${characterLeft}</span> {{ translate('Left') }}`);
            } else {
                worCountElem.empty()
            }
        }
    </script>
</body>

</html>
