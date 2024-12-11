@extends('layouts.frontend')
@section('content')
    <div class="px-0 col-12 col-md-12 col-lg-6 col-xl-6">
        <div class="login-left-section d-flex align-items-center justify-content-center">
            <div class="form-container">
                <div>
                    <div class="mb-3">
                        <h4>{{ translate('Sign Up With') }} <span
                                class="site--title">{{ ucfirst($general->site_name) }}</span></h4>
                    </div>
                    @if (config('app.modules.google_login'))
                        <div class="my-3">
                            <a class="p-2 rounded shadow-sm d-flex text-decoration-none text-dark align-items-center justify-content-center google--login"
                                href="{{ url('auth/google') }}">
                                <div class="d-flex align-items-center justify-content-center google--login--text">
                                    <div class="google-img me-2">
                                        <img src="{{ showImage('assets/frontend/img/google.png') }}" alt=""
                                            class="w-100">
                                    </div>{{ translate('Continue with google') }}
                                </div>
                            </a>
                        </div>
                        <div class="text-center or">
                            <p class="m-0">{{ translate('Or') }}</p>
                        </div>
                    @endif
                </div>

                <form action="{{ route('registration.verify') }}" method="POST">
                    @csrf
                    <div class="my-3">
                        <label for="name" class="form-label d-block">{{ translate('Name') }}</label>
                        <div class="d-flex align-items-center border-bottom">
                            <i class="las la-envelope-open-text fs-3 text-primary"></i>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="{{ translate('Enter Name') }}" class="p-2 border-0 w-100" id="name"
                                required />
                        </div>
                    </div>

                    <div class="my-3">
                        <label for="email" class="form-label d-block">{{ translate('Email') }}</label>
                        <div class="d-flex align-items-center border-bottom">
                            <i class="las la-envelope-open fs-3 text-primary"></i>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="{{ translate('Enter your email address') }}" class="p-2 border-0 w-100"
                                id="email" required />
                        </div>
                    </div>

                    <div class="my-3">
                        <x-phone-input id="phone" name="phone" class="w-100" countryCode="phone_code"
                                       ria-describedby="phoneHelp"
                                       placeholder="{{ translate('Enter your WhatsApp no.')}}" class="border-0 w-100">
                        </x-phone-input>
                        <small id="phoneHelp" style="font-size: 0.7em;">Enter number without dashes, spaces, or
                            brackets. e.g. 1876123456</small>

                    </div>

                    <button type="submit"
                        class="mt-2 shadow btn btn--info w-100 text-light">{{ translate('Register') }}</button>
                </form>
                <p class="mt-3 text-center">
                    {{ translate('Already have an account') }}? <a
                        href="{{ route('login') }}">{{ translate('Sign In') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
