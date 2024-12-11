@php
    $generalSettings = \App\Models\GeneralSetting::admin();
    $gatewayStatus = $generalSettings->getWhatsappGatewayStatus();

    $user = auth()->user();
    $isAntiBlockEnabled = \Illuminate\Support\Arr::get($user->data, 'whatsapp.anti_block');
    if($isAntiBlockEnabled === null)
    {
        $user->data = $user->data ?: [];
        \Illuminate\Support\Arr::set($user->data, 'whatsapp.anti_block', true);
        $user->saveQuietly();
        $isAntiBlockEnabled = true;
    }
@endphp


<div class="row col-12">
    <div class="col-xl-6 my-2">
        <div class="single_pinned_project  my-2 shadow">
            <div class="pinned_icon">
                <i class="fab fa-info"></i>
            </div>
            <div class="pinned_text">
                <div>
                    <h6>Web Gateway</h6>
                    <div class="text-secondary my-1">Easiest to connect - Free - unsecured - Unreliable - Watermarked (Removable upon adding credit).
                    </div>
                    <a href="{{ route('docs.whatsapp-gateway') }}#web" target="_blank">Learn More</a>
                </div>
            </div>
        </div>
        <div class="single_pinned_project my-2  shadow">
            <div class="pinned_icon">
                <i class="fab fa-info"></i>
            </div>
            <div class="pinned_text">
                <div>
                    <h6>PC Gateway</h6>
                    <div class="text-secondary my-1">Requires PC client Download - Secure - Free - Requires PC to be on
                        and in active state - For 24/7 operations will require hosting on Windows server - Watermarked (Removable upon adding credit).
                    </div>
                    <div class="d-flex gap-4">
                    <a href="{{ route('docs.whatsapp-gateway') }}#desktop" target="_blank">Learn More</a>
                    <a href="{{ asset('assets/desktop-app/SendEach.msi') }}" target="_blank">Download
                        Desktop APP</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 my-2">
        <div class="single_pinned_project my-2 shadow">
            <div class="pinned_icon">
                <i class="fab fa-info"></i>
            </div>
            <div class="pinned_text">
                <div>
                    <h6>SendEach WhatsApp API Gateway</h6>
                    <div class="text-secondary my-1">Paid - Most reliable and secure - Easiest - No watermark.
                    </div>
                    <a href="{{ route('docs.whatsapp-gateway') }}#sendeach-api" target="_blank">Learn More</a>
                </div>
            </div>
        </div>
        <div class="single_pinned_project  my-2 shadow">
            <div class="pinned_icon">
                <i class="fab fa-info"></i>
            </div>
            <div class="pinned_text">
                <div>
                    <h6>Own WhatsApp API Gateway</h6>
                    <div class="text-secondary my-1">Free - Hardest to Setup - Secure and Reliable - Watermarked (Removable upon adding credit).
                    </div>
                    <a href="{{ route('docs.whatsapp-gateway') }}#business-api" target="_blank">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    @if(\Illuminate\Support\Arr::get(@$gatewayStatus, \App\Models\WhatsappLog::GATEWAY_DESKTOP) == \App\Models\WhatsappLog::GATEWAY_STATUS_OFFLINE)
    <div class="alert m-3 alert-danger">
        The WhatsApp gateway for your computer is currently offline. We are working to resolve the issue as soon as possible. In the meantime, please switch to another online gateway to continue using WhatsApp.
        <br>You can always choose our SendEach Business gateway for more reliable communications.
    </div>
    @endif

    @if(\Illuminate\Support\Arr::get(@$gatewayStatus, \App\Models\WhatsappLog::GATEWAY_WEB) == \App\Models\WhatsappLog::GATEWAY_STATUS_OFFLINE)
        <div class="alert m-3 alert-danger">
            The WhatsApp Web Gateway is currently unavailable. We are working to resolve the issue as soon as possible. In the meantime, please switch to another online gateway to continue using WhatsApp.
            <br>You can always choose our SendEach Business gateway for more reliable communications.
        </div>
    @endif

    <div class="col-xl-6">
        <form action="{{route('user.gateway.whatsapp.marketing_update_default')}}" method="POST"
              enctype="multipart/form-data">
            @csrf
            <div class="card mb-2">
                <div class="card-header">
                    {{ translate('Choose Whatsapp Default Marketing Gateway')}}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="whatsapp_gateway"
                                   class="form-label">{{translate('Default Whatsapp Gateway')}} <sup
                                    class="text--danger">*</sup></label>
                            <p class="text-secondary py-2">This Gateway will be responsible for delivering all messages sent from your account, whether sent via our web interface or our APIs.</p>
                            <select class="form-select" id="whatsapp_gateway" name="whatsapp_gateway"
                                    required="">
                                <option disabled selected>{{translate('Select Gateway')}}</option>
                                @foreach(\App\Models\WhatsappLog::GATEWAYS as $gateway)
                                    <option value="{{ $gateway }}"
                                            @if(auth()->user()->default_whatsapp_gateway == $gateway) selected @endif>{{\App\Models\WhatsappLog::GATEWAY[$gateway]}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <button type="submit"
                                    class="btn col-6 m-auto btn--primary me-sm-3 me-1 float-end">{{ translate('Update')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-xl-6">
        <form action="{{ route('user.whatsapp.messages.auto_delete.toggle') }}"
              id="toggle_auto_delete_form"
              method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card mb-2">
                <div class="card-header">
                    {{ translate('Enable Auto Delete')}}
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label
                            class="form-label">{{translate('Enable Auto WhatsApp Message Delete')}} <sup
                                class="text--danger">*</sup></label>
                        <p class="text-secondary py-2">
                            This feature enables automatic deletion of any WhatsApp messages sent
                            through your PC gateway, eliminating the need for manual deletion.
                        </p>
                        <br>
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input"
                                   onchange="$('#toggle_auto_delete_form').submit()"
                                   name="is_enabled" type="checkbox"
                                   @if(auth()->user()->auto_delete_whatsapp_pc_messages) checked @endif
                                   id="is_enabled">
                            <label class="form-check-label" for="toggleButton">Enable Now</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="alert alert-warning">
                        The WhatsApp messages are deleted from <b class="fw-bold">Web Gateway</b>. Make sure that
                        your whatsapp account is connected in web gateway before you use this feature.
                    </div>
                    <a class="link-primary float-end" href="{{ route('user.whatsapp.messages.delete.index') }}">Search and Delete Sent Messages</a>
                </div>
            </div>
        </form>
    </div>
    @if(auth()->user()->default_whatsapp_gateway === \App\Models\WhatsappLog::GATEWAY_BUSINESS_OWN)
        <div class="col-xl-6">
            <form action="{{route('user.business.whatsapp.template.update_otp_template')}}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <div class="card mb-2">
                    <div class="card-header">
                        {{ translate('Choose Your OTP Template to be used.')}}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-12">
                                <label for="whatsapp_template_id"
                                       class="form-label">{{translate('Whatsapp Business OTP Template')}}
                                    <sup
                                        class="text--danger">*</sup></label>
                                <p class="text-secondary py-2">This template will be used to send OTP
                                    whatsapp
                                    messages from Client API.</p>
                                <select class="form-select" id="whatsapp_template_id"
                                        name="whatsapp_template_id"
                                        required="">
                                    <option disabled
                                            selected>{{translate('Select Whatsapp Template')}}</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->whatsapp_template_id }}"
                                                @if(auth()->user()->whatsapp_business_otp_template_id == $template->whatsapp_template_id) selected @endif>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 alert alert-danger">
                                <li>Your template Body Component should have a variable to get OTP
                                    value.
                                </li>
                                <li>The Other Components should not take any variables.</li>
                                <li>Otherwise, the OTP won't be sent.</li>
                                <li> However, the structure will be changed in future if necessary.</li>
                            </div>
                            <div class="row">
                                <button type="submit"
                                        class="btn col-6 m-auto btn-primary me-sm-3 me-1 float-end">{{ translate('Update')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <div class="col-xl-6">
        <form action="{{ route('user.whatsapp.messages.anti_block.toggle') }}"
              id="antiblock_form"
              method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card mb-2">
                <div class="card-header">
                    {{ translate('Enable Anti Block')}}
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label
                            class="form-label" for="is_anti_block_enabled">{{translate('Enable Anti Block System')}} <sup
                                class="text--danger">*</sup></label>
                        <p class="text-secondary py-2">
                            This feature utilizes AI to generate different versions of same messages when sending to 100s of users at same time.
                        </p>
                        <br>
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input"
                                   onchange="$('#antiblock_form').submit()"
                                   name="is_enabled" type="checkbox"
                                   @if(@$isAntiBlockEnabled) checked @endif
                                   id="is_anti_block_enabled">
                            <label class="form-check-label" for="toggleButton">Enable Now</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="alert alert-warning">
                        Make sure you have available OpenAI tokens or credits to use this feature.
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>
