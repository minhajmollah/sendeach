<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__($general->site_name)}} - {{__($title)}}</title>
    <script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <meta name="csrf-token" content="{{csrf_token()}}" />
    <link rel="shortcut icon" href="{{showImage(filePath()['site_logo']['path'].'/site_favicon.png')}}" type="image/x-icon">
    <link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/global/css/toastr.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/style.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/responsive.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/select2.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/apexcharts.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/datepicker/datepicker.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/summernote-lite.min.css')}}?v={{ config('app.asset_version') }}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/flag-icons/flag-icons.css')}}?v={{ config('app.asset_version') }}">
    @stack('style-include')
    @stack('stylepush')
    <style>
        .la-exclamation-triangle:before {
            background: #f95454;
            content: "\f071";
            color: #fff;
            padding: 10px 11px;
            font-size: 43px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    @yield('content')
    <div class="modal fade" id="confirmEmailModel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
    		<div class="modal-content">
    			<form action="{{route('user.confirm.email')}}" method="POST">
    				@csrf
    				<input type="hidden" name="id">
    				<div class="modal_body2">
    					<div class="modal_icon2">
    						<i class="las la-exclamation-triangle"></i>
    					</div>
    					<div class="modal_text2 mt-3">
    						<h6>{{ translate('Please confirm your email!') }}</h6>
    					</div>
    					<div class="modal_text2 mt-3">
    						<input type="text" class="form-control" id="email" name="email" placeholder="{{ translate('Enter your email') }}" required>
    					</div>
    				</div>
    				<div class="modal_button2">
    					<button type="submit" class="bg--danger">{{ translate('Confirm') }}</button>
    				</div>
    			</form>
    		</div>
    	</div>
    </div>
    <script src="{{asset('assets/global/js/all.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/global/js/toastr.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/global/js/bootstrap.bundle.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/chart.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/apexcharts.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/select2.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/main.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/datepicker/datepicker.min.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/datepicker/datepicker.en.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/ckeditor.js')}}?v={{ config('app.asset_version') }}"></script>
    <script src="{{asset('assets/dashboard/js/summernote-lite.min.js')}}?v={{ config('app.asset_version') }}"></script>

    <link href="https://cdn.datatables.net/v/bs5/dt-1.13.6/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.6/datatables.min.js"></script>

    <!--<script src="https://www.google.com/recaptcha/api.js" async defer></script>-->
    @include('partials.notify')
    @stack('script-include')
    @stack('scriptpush')
    <script type="text/javascript">
        'use strict';
        function changeLang(val){
            window.location.href = "{{route('login')}}/language/change/"+val;
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
            let worCountElem = input.parent().parent().find(".message--word-count")
            console.log(worCountElem)
            let character = input.val();
            let totalLength = input.data('max-length') ?? 1000
            let characterLeft = totalLength - character.length;
            let word = character.split(" ");
            let wordLength = character.length
            let status = 'primary';

            if (characterLeft < 0) {
                status = 'danger';
            }

            if(!worCountElem.length)
            {
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
