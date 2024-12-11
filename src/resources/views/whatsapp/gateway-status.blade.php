@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <form action="{{ route('admin.gateway.whatsapp.status.update') }}" method="POST">
                <div class="row">
                    <div class="alert alert-primary">
                        Set the WhatsApp gateway status to active or inactive. This will allow users to know the status of the gateway and take appropriate action.
                    </div>
                    <div class="row col-12">
                        @method('PUT')
                        @csrf
                        <div class="mb-3 col col-md-6">
                            <label for="web_gateway_status"
                                   class="form-label">{{translate('Whatsapp Web Gateway Status')}} <sup
                                    class="text--danger">*</sup></label>

                            <select class="form-select" id="web_gateway_status" name="web_gateway_status"
                                    required="">
                                required="">
                                @foreach(\App\Models\WhatsappLog::GATEWAY_STATUS as $key => $status)
                                    <option value="{{ $key }}" @selected($gatewayStatus[\App\Models\WhatsappLog::GATEWAY_WEB] == $key)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col col-md-6">
                            <label for="pc_gateway_status"
                                   class="form-label">{{translate('Whatsapp PC Gateway Status')}} <sup
                                    class="text--danger">*</sup></label>

                            <select class="form-select" id="pc_gateway_status" name="pc_gateway_status"
                                    required="">
                                @foreach(\App\Models\WhatsappLog::GATEWAY_STATUS as $key => $status)
                                    <option value="{{ $key }}" @selected($gatewayStatus[\App\Models\WhatsappLog::GATEWAY_DESKTOP] == $key)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn--primary">Update</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </section>

@endsection

@push('scriptpush')


@endpush
