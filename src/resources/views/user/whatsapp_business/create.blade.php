@extends('user.layouts.app')
@section('panel')
    <script>
        window.fbAsyncInit = function () {
            // JavaScript SDK configuration and setup
            FB.init({
                appId: '{{ config('whatsapp.app_id') }}', // Facebook App ID
                cookie: true, // enable cookies
                xfbml: true, // parse social plugins on this page
                version: 'v16.0' //Graph API version
            });
        };

        // Load the JavaScript SDK asynchronously
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        // Facebook Login with JavaScript SDK
        function launchWhatsAppSignup() {
            // Launch Facebook login
            FB.login(function (response) {
                if (response.authResponse.accessToken) {
                    //Use this token to call the debug_token API and get the shared WABA's ID
                    storeAccessToken(response)
                } else {
                    console.log('User cancelled login or did not fully authorize.');
                }
            }, {
                scope: 'business_management,whatsapp_business_management',
                extras: {
                    feature: 'whatsapp_embedded_signup',
                }
            });
        }

        function storeAccessToken(accessToken) {

            // accessToken = {
            //     "authResponse": {
            //         "accessToken": "EAAAAKvQdWksBAIZAXBZAIG5JBgzteyyJJ2MW3tHYlKdlUehEzF4GLWmkd71IZAZCF8QWtNjwkzZBjaHlF2ZCfoWG2bW9ZAszTMlcKtZAxgK4Ftdp7JwZA8yKM78YJc2rBrWkAobw9KfBZA4EmdLmPf83cZCStXcLUyv57JO3S0iHKzljEnOD5PDTr0q2mJr4RlH7RzEtId0ZCTapjlTTUsxiODsIDHyq4I1mZAnpMOZCL9RDx82gZDZD",
            //         "userID": "2230627423783880",
            //         "expiresIn": 7112,
            //         "signedRequest": "vzCzQU-b71Ck_-ONIYfkZskEDVVwTiTVpDrgR3v7pE4.eyJ1c2VyX2lkIjoiMjIzMDYyNzQyMzc4Mzg4MCIsImNvZGUiOiJBUUF2aW85U3U0bkVrdEI5WWVzRWltYUFiYlFLekU3NlhXZ1AyeDFCVEtFR0cxYlRBLUFPSG5KUWEybkxnNFNEOHpBc0JGUGxucUNtQXl5endYdFdJZTFHdUlmdUNPVE9kbG1oMDd3aXhicUpWenVVdGtkSnEyeGpkSjNGcUJUYjJkaXhHNmQ5RW5CbHR0QzRBcDY0MTgzN0hYdERZM1lZMU9GZjRDTG5JcDZMWndhOWFWVF81dm1kVVkwSE1DQTA2RjFGN1JGV2Zldm5WeHlEZERqQW4ta29JSzl6NGxFUEF0bUFTcDl3eEJwellKanVaR1dTaENzaXN1a2VwZTdqODRJa3dpRDdHem1iMU9DdlVRRTB3cl9Nd29QZDRFTW13T3VrMUdPZUozWUsxcUpXUjJScFNSS2FUSW5ac2F3Rll1V25tTzlDRDdXZkFHdlZaTkNISGNyY1U5SGt1aEVQWXRpbUc4bkdLTHJTN0EiLCJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTY4MjM1MjA4OH0",
            //         "graphDomain": "facebook",
            //         "data_access_expiration_time": 1690128088
            //     },
            //     "status": "connected",
            //     "loginSource": "facebook"
            // };

            $.ajax({
                url: '{{ route('user.business.whatsapp.account.embedded_access_token.store') }}',
                data: accessToken.authResponse,
                dataType: "json",
                type: "POST",
                async: true,
                headers: {
                    'X-CSRF-TOKEN': "{{csrf_token()}}",
                },
                success: function (response) {
                    alert('Successfully added Access Token');
                    alert('Your Account is in sync, You can start sending message after 2-3 Mins.');
                    window.location.reload();
                },
                error: function (response) {
                    let res = response.responseJSON
                    console.log(res)

                    if (res.message) {
                        alert(res.message);
                    }

                    if (res.errors && res.errors.accessToken) {
                        alert(res.errors.accessToken)
                    }
                }
            });

            // let form = $("#access_token_form");
            //
            // form.find("#access_token_type").val('EMBEDDED_FORM');
            // form.find("#access_token").prop('disabled', false);
            // form.find("#access_token").val(accessToken);
            //
            // form.submit();
        }
    </script>

    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                @include('user.partials.whatsapp-default-settings-select')

                @if(auth()->user()->default_whatsapp_gateway === \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN)
                    <div class="row col-12">
                        <div class="col">
                            <div class="card mb-2">
                                <div class="card-header">
                                    {{ translate('WhatsApp Business Accounts List')}}
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th>{{ translate('Name')}}</th>
                                                <th>{{ translate('Whatsapp Business ID')}}</th>
                                                <th>{{ translate('Whatsapp Template Namespaces')}}</th>
                                                <th>{{ translate('Timezone ID')}}</th>
                                                {{-- <th>{{ translate('Multi Device')}}</th> --}}
                                                <th>{{ translate('Action')}}</th>
                                            </tr>
                                            @forelse ($whatsappAccounts as $item)
                                                <tr id="whatsapp_account_{{ $item->whatsapp_business_id }}">
                                                    <td style="padding-left: 1rem !important;">{{$item->name}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->whatsapp_business_id}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->message_template_namespace}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->timezone_id}}</td>
                                                    <td style="padding-left: 1rem !important;">
                                                        <a title="Delete" href=""
                                                           class="badge bg-danger p-2 whatsappDelete"
                                                           value="{{$item->whatsapp_business_id}}"><i
                                                                class="fas fa-trash-alt"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="50"><span
                                                            class="text-danger">{{ translate('No data Available')}}</span>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </table>
                                    </div>
                                    <div class="m-3">
                                        {{$whatsappAccounts->appends(request()->all())->links()}}
                                    </div>
                                </div>
                                <div class="card-footer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="card mb-2">
                                <div class="card-header">
                                    {{ translate('WhatsApp Business Phone Number List')}}
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th>{{ translate('Name')}}</th>
                                                <th>{{ translate('Number')}}</th>
                                                <th>{{ translate('Whatsapp Business ID')}}</th>
                                                <th>{{ translate('Whatsapp Number ID')}}</th>
                                                <th>{{ translate('Status')}}</th>
                                                <th>{{ translate('Action')}}</th>
                                            </tr>
                                            @forelse ($whatsappNumbers as $item)
                                                <tr id="whatsappDevice_{{ $item->whatsapp_phone_number_id }}">
                                                    <td style="padding-left: 1rem !important;">{{ucfirst($item->verified_name)}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->display_phone_number}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->whatsapp_business_id}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->whatsapp_phone_number_id}}</td>
                                                    <td style="padding-left: 1rem !important;">{{$item->code_verification_status}}</td>
                                                    <td style="padding-left: 1rem !important;">
                                                        @if(!$item->isActive())
                                                            <a title="Register" href="#"
                                                               class="badge bg-primary p-2 registerPhone"
                                                               value="{{$item->whatsapp_phone_number_id}}"><i
                                                                    class="fas fa-anchor me-1"></i>Activate</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="50"><span
                                                            class="text-danger">{{ translate('No data Available')}}</span>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </table>
                                    </div>
                                    <div class="m-3">
                                        {{$whatsappNumbers->appends(request()->all())->links()}}
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('user.business.whatsapp.account.sync') }}"
                                       class="btn btn-primary me-sm-3 me-1 float-end">{{ translate('Sync Whatsapp Numbers, Message Templates from Meta Business Account')}}</a>
                                </div>
                            </div>
                        </div>

                    </div>

                    @if(config('whatsapp.enable_embedded_form'))
                        <div class="row">
                            <div class="col">
                                <div class="card mb-2">
                                    <div class="card-header">
                                        {{ translate('Easy WhatsApp Business Account/Phone Management')}}
                                    </div>
                                    <div class="card-body">
                                        @if($hasAccessEmbeddedToken)
                                            <div class="alert alert-success">Whatsapp Business Account is active. You
                                                are
                                                ready
                                                to
                                                Send Messages. Goto
                                                <a href="{{ route('user.business.whatsapp.create') }}">Send Business
                                                    Whatsapp</a> to
                                                send Message.
                                            </div>
                                        @else
                                            <div class="alert alert-primary">Please create your Meta Whatsapp Business
                                                Account
                                                by
                                                clicking below button.
                                            </div>
                                        @endif
                                        <div>
                                            <li class="fs-6">Simple and Easy way to create your whatsapp Business
                                                account
                                                and
                                                business profile. Just Click the below button to get started.
                                            </li>
                                            <li class="fs-6">Create your business profile with existing whatsapp App
                                                registered
                                                business phone number.
                                            </li>
                                            <li class="fs-6">After You create your first Business Account and Whatsapp
                                                Business
                                                Profile, You can start adding multiple Phone Numbers.
                                            </li>
                                        </div>
                                        <div class="fs-5 mt-4">
                                            <h6 class="text-danger h6 fw-bold">Note: </h6>
                                            <li class="text-danger fs-6">You should create your Whatsapp Business
                                                Account
                                                with
                                                existing Whatsapp Business App Phone Number.
                                            </li>
                                            <li class="text-danger fs-6">You cannot add a Phone if Phone number is
                                                already
                                                linked to
                                                existing Whatsapp Business Account or registered to Whatsapp.
                                            </li>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <button onclick="launchWhatsAppSignup()"
                                                style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; height: 35px; padding: 0 20px;">
                                            Create/Update System User Access Token
                                        </button>
                                        <button onclick="launchWhatsAppSignup()"
                                                style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; height: 35px; padding: 0 20px;">
                                            Create/Update Meta Whatsapp Business Account
                                        </button>
                                        <button onclick="launchWhatsAppSignup()"
                                                style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 14px; font-weight: bold; height: 35px; padding: 0 20px;">
                                            Add Phone Number
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif

                @endif
            </div>
        </div>
    </section>

    @if(auth()->user()->default_whatsapp_gateway === \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN)
        <section class="mt-3 rounded_box">
            <h5 class="h5">Your Own Business Account created from Meta Business Manager</h5>
            <div class="alert alert-primary">Please read the docs <a target="_blank" class="text-decoration-underline"
                                                                     href="{{ route('docs.business.whatsapp') }}">here.</a>
                To know how to get this token.
            </div>
            <div class="container-fluid p-0 mb-3 pb-2">
                <div class="row mt-4 justify-content-center">
                    <div class="col-xl-6">
                        <form action="{{route('user.business.whatsapp.account.access_token.store')}}" method="POST"
                              id="access_token_form"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="card mb-2">
                                <div class="card-header">
                                    @if(!$hasAccessOwnToken)
                                        <p class="text-danger fw-bold fs-5">No Access Token added</p>
                                    @endif
                                    <p class="text-muted fs-6"> {{ translate('Create this and get this from your Meta Business Account Manager')}}</p>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-4">
                                            <label for="access_token">{{ translate('System User Access Token')}} <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="hidden" id="access_token_type" value="OWN" name="type">
                                                <input type="password"
                                                       class="form-control @error('access_token') is-invalid @enderror "
                                                       name="accessToken" id="access_token"
                                                       value="{{old('accessToken')}}"
                                                       placeholder="{{ translate('OAuth Access Token')}}"
                                                       @if($hasAccessOwnToken) disabled @endif>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary"
                                                            onclick="enableAccessTokenEdit()"
                                                            type="button">Edit <i class="fas fa-edit"></i></button>
                                                </div>
                                            </div>
                                            @error('accessToken')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <button type="submit" id="access_token_btn"
                                            class="btn btn-primary me-sm-3 me-1 float-end"
                                            @if($hasAccessOwnToken) disabled @endif>
                                        @if($hasAccessOwnToken)
                                            {{ translate('Update')}}
                                        @else
                                            {{ translate('Add')}}
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    {{--                <div class="col-xl-6">--}}
                    {{--                    <form action="{{route('user.business.whatsapp.account.store')}}" method="POST"--}}
                    {{--                          enctype="multipart/form-data">--}}
                    {{--                        @csrf--}}
                    {{--                        <div class="card mb-2">--}}
                    {{--                            <div class="card-header">--}}
                    {{--                                {{ translate('Whatsapp Business Account Add')}}--}}
                    {{--                                <p class="text-muted">Get this from your Meta Whatsapp Business Account Manager</p>--}}
                    {{--                            </div>--}}
                    {{--                            <div class="card-body">--}}
                    {{--                                <div class="row">--}}
                    {{--                                    <div class="col-md-12 mb-4">--}}
                    {{--                                        <label for="whatsapp_business_id">{{ translate('Whatsapp Business ID')}} <span--}}
                    {{--                                                class="text-danger">*</span></label>--}}
                    {{--                                        <input type="text"--}}
                    {{--                                               class="form-control @error('whatsapp_business_id') is-invalid @enderror "--}}
                    {{--                                               name="whatsapp_business_id" id="whatsapp_business_id"--}}
                    {{--                                               value="{{old('whatsapp_business_id')}}"--}}
                    {{--                                               placeholder="{{ translate('WABAID')}}">--}}
                    {{--                                        @error('whatsapp_business_id')--}}
                    {{--                                        <span class="text-danger">{{$message}}</span>--}}
                    {{--                                        @enderror--}}
                    {{--                                    </div>--}}
                    {{--                                </div>--}}
                    {{--                                <button type="submit"--}}
                    {{--                                        class="btn btn-primary me-sm-3 me-1 float-end">{{ translate('Add')}}</button>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                    </form>--}}
                    {{--                </div>--}}
                </div>
            </div>
        </section>
    @endif

    {{-- whatsapp delete modal --}}
    <div class="modal fade" id="whatsappDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.business.whatsapp.account.delete')}}" method="POST">
                    @csrf
                    @method('delete')
                    <input type="hidden" name="whatsapp_business_id" value="">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete')}}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="bg--danger">{{ translate('Delete')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registerPhone" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.business.whatsapp.account.phones.register')}}" method="POST">
                    @csrf
                    <input type="hidden" name="whatsapp_phone_number_id" value="">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-phone fs--3"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Please enter the 6 digit Pin sent to you.')}}</h6>
                        </div>
                        <div class="col-md-12 mb-4">
                            <input type="number"
                                   class="form-control"
                                   name="pin" id="pin"
                                   placeholder="{{ translate('6 Digit Pin')}}">
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="bg--primary">{{ translate('Register')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection


@push('scriptpush')
    <script>
        function enableAccessTokenEdit() {
            $('#access_token').prop('disabled', false);
            $('#access_token_btn').prop('disabled', false);
        }

        (function ($) {
            "use strict";
            $(document).on('click', '.whatsappDelete', function (e) {
                e.preventDefault()
                var id = $(this).attr('value')
                var modal = $('#whatsappDelete');
                modal.find('input[name=whatsapp_business_id]').val(id);
                modal.modal('show');
            })

            $(document).on('click', '.registerPhone', function (e) {
                e.preventDefault()
                var id = $(this).attr('value')
                var modal = $('#registerPhone');
                modal.find('input[name=whatsapp_phone_number_id]').val(id);
                modal.modal('show');
            })
        })(jQuery);
    </script>

@endpush

