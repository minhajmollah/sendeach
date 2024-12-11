@props(['route' => null, 'maxAttempts' => 1])
@if(getAccessAttempts($route.':'.request()->getClientIp()) >= $maxAttempts)
    <div class="d-flex my-4 justify-content-center align-items-center flex-column"> {!! NoCaptcha::display() !!}
        @if ($errors->has('g-recaptcha-response'))
            <span class="text-danger">
                {{ $errors->first('g-recaptcha-response') }}
            </span>
        @endif
    </div>
@endif

@pushonce('scripts')
    {!! NoCaptcha::renderJs() !!}
@endpushonce
