@php
    $user = auth()->user();
    $isAntiBlockEnabled = \Illuminate\Support\Arr::get($user->data, 'sms.anti_block');
    if($isAntiBlockEnabled === null)
    {
        $user->data = $user->data ?: [];
        \Illuminate\Support\Arr::set($user->data, 'sms.anti_block', true);
        $user->saveQuietly();
        $isAntiBlockEnabled = true;
    }
@endphp

@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="p-0 container-fluid">
            <div class="row">
                <div class="col-xl-6">
                    <form action="{{route('user.sms.default.gateway')}}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('Choose SMS Default Gateway')}}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label for="sms_gateway"
                                               class="form-label">{{translate('SMS Default Gateway')}} <sup
                                                class="text--danger">*</sup></label>
                                        <p class="text-secondary py-2">This Gateway will be responsible for delivering
                                            all messages sent from your account, whether sent via our web
                                            interface or our APIs.</p>
                                        <select class="form-select" id="sms_gateway" name="sms_gateway"
                                                required="">
                                            <option disabled selected>{{translate('Select Gateway')}}</option>
                                            @foreach($smsGateways as $gateway)
                                                <option value="{{ $gateway->id }}"
                                                        @if($gateway->default_use) selected @endif>{{ $gateway->name }}</option>
                                            @endforeach
                                                <option value="0" @if(!$smsGateways->where('default_use', 1)->first()) selected @endif>Android</option>
                                        </select>
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
                <div class="col-xl-6">
                    <form action="{{ route('user.sms.gateway.anti_block.toggle') }}"
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

                <div class="p-1 col-lg-12">
                    <div class="mb-4 rounded_box">
                        <div class="px-2 py-3">
                            <h6>{{ translate('Android Devices')}}
                            </h6>
                            <a
                                href="{{ asset('assets/android/v001.apk') }}" class="btn btn--primary me-4"
                                download="SendEach Mobile - Android App.apk" target="_blank">Download APK</a>
                            <button class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#learnAndroidApp">
                                Connect Android
                            </button>
                        </div>
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th>{{ translate('#') }}</th>
                                    <th>{{ translate('Device ID') }}</th>
                                    <th>{{ translate('Created At') }}</th>
                                    <th>{{ translate('Action') }}</th>
                                </tr>
                                </thead>
                                @forelse($devices as $device)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td data-label="{{ translate('#') }}">
                                            {{ $loop->index + 1 }}
                                        </td>

                                        <td data-label="{{ translate('Device Id') }}">
                                            {{__($device->device_id)}}
                                        </td>

                                        <td data-label="{{ translate('Created At') }}">
                                            {{__($device->created_at->format('d-M-Y h:i A'))}}
                                        </td>

                                        <td data-label="{{ translate('Action') }}">
                                            <a title="Delete" href="" class="badge bg-danger p-2 delete"
                                               data-value="{{ $device->id }}"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted"
                                            colspan="100%">{{ translate('No Data Found') }}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{$devices->appends(request()->all())->links()}}
                        </div>
                    </div>
                </div>
                <div class="pt-3 col-lg-12">
                    <div class="rounded_box">
                        <h6 class="my-3">{{ translate('Third Party API Gateways')}}</h6>
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th>{{ translate('Gateway Name')}}</th>
                                    <th>{{ translate('Status')}}</th>
                                    <th>{{ translate('Action')}}</th>
                                </tr>
                                </thead>
                                @forelse($smsGateways as $smsGateway)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td data-label="{{ translate('Gateway Name')}}">
                                            {{ucfirst($smsGateway->name)}}
                                            @if($smsGateway->default_use == 1)
                                                <span class="text--success fs-5">
					                    		<i class="las la-check-double"></i>
					                    	</span>
                                            @endif
                                        </td>

                                        <td data-label="{{ translate('Status')}}">
                                            @if($smsGateway->status == 1)
                                                <span class="badge badge--success">{{ translate('Active')}}</span>
                                            @else
                                                <span class="badge badge--danger">{{ translate('Inactive')}}</span>
                                            @endif
                                        </td>

                                        <td data-label={{ translate('Action')}}>
                                            <a href="{{route('user.gateway.sms.edit', $smsGateway->id)}}"
                                               class="btn--primary text--light brand" data-bs-toggle="tooltip"
                                               data-bs-placement="top" title="Edit"><i class="las la-pen"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted"
                                            colspan="100%">{{ translate('No Data Found')}}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{$smsGateways->appends(request()->all())->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="learnAndroidApp" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div
                                    class="text-center card-title text--light">{{ translate('Link Android Device with SendEach') }}</div>
                            </div>
                            <div class="card-body">
                                <p>To link an android devices with SendEach, follow the below listed steps!</p>

                                <ol class="my-3">
                                    <li>Download the APK file by clicking this link, <a
                                            href="{{ asset('assets/android/v001.apk') }}"
                                            download="SendEach Mobile - Android App.apk" target="_blank">Download
                                            APK</a>.
                                    </li>
                                    <li>Install the downloading application and give required permissions.</li>
                                    <li>Login to the application using your account detials.</li>
                                    <li>Click on the connect to complete the connection.</li>
                                    <li>Refresh this page and you will see your connected devices in the bottom table.
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('user.sms.gateway.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" value="">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete') }}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="bg--danger">{{ translate('Delete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@pushonce('scriptpush')
    <script>
        $(document).on('click', '.delete', function (e) {
            e.preventDefault()
            let id = $(this).data('value')
            let modal = $('#delete');
            modal.find('input[name=id]').val(id);
            modal.modal('show');
        })
    </script>
@endpushonce
