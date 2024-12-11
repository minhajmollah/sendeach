@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="p-0 container-fluid">
            <div class="row">
                <div class="p-1 col-lg-12">
                    <div class="mb-4 rounded_box">
                        <div class="px-2 py-3">
                            <h6>{{ translate('Android Devices') }}
                            </h6>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#learnAndroidApp">Connect
                                Android</button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#attachAndroidDevice">Admin Connection</button>
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
                                    <tr class="@if ($loop->even) table-light @endif">
                                        <td data-label="{{ translate('#') }}">
                                            {{ $loop->index + 1 }}
                                        </td>

                                        <td data-label="{{ translate('Device Id') }}">
                                            {{ __($device->device_id) }}
                                        </td>

                                        <td data-label="{{ translate('Created At') }}">
                                            {{ __($device->created_at->format('d-M-Y h:i A')) }}
                                        </td>

                                        <td data-label="{{ translate('Action') }}">
                                            <a href="{{ route('admin.gateway.remove_device', $device) }}" class="btn btn-danger"><i class="fa fa-trash"></i> Detach</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted" colspan="100%">{{ translate('No Data Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{ $devices->appends(request()->all())->links() }}
                        </div>
                    </div>
                </div>
                <div class="pt-3 col-lg-12">
                    <div class="rounded_box">
                        <h6 class="my-3">{{ translate('Third Party API Gateways') }}</h6>
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Gateway Name') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Action') }}</th>
                                    </tr>
                                </thead>
                                @forelse($smsGateways as $smsGateway)
                                    <tr class="@if ($loop->even) table-light @endif">
                                        <td data-label="{{ translate('Gateway Name') }}">
                                            {{ ucfirst($smsGateway->name) }}
                                            @if ($smsGateway->default_use == 1)
                                                <span class="text--success fs-5">
                                                    <i class="las la-check-double"></i>
                                                </span>
                                            @endif
                                        </td>

                                        <td data-label="{{ translate('Status') }}">
                                            @if ($smsGateway->status == 1)
                                                <span class="badge badge--success">{{ translate('Active') }}</span>
                                            @else
                                                <span class="badge badge--danger">{{ translate('Inactive') }}</span>
                                            @endif
                                        </td>

                                        <td data-label={{ translate('Action') }}>
                                            <a href="{{ route('admin.gateway.sms.edit', $smsGateway->id) }}"
                                                class="btn--primary text--light brand" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Edit"><i class="las la-pen"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted" colspan="100%">{{ translate('No Data Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{ $smsGateways->appends(request()->all())->links() }}
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
                                <div class="text-center card-title text--light">
                                    {{ translate('Link Android Device with SendEach') }}</div>
                            </div>
                            <div class="card-body">
                                <p>To link an android devices with SendEach, follow the below listed steps!</p>

                                <ol class="my-3">
                                    <li>Download the APK file by clicking this link, <a
                                            href="{{ asset('assets/android/v001.apk') }}"
                                            download="SendEach Mobile - Android App.apk" target="_blank">Download APK</a>.
                                    </li>
                                    <li>Install the downloading application and give required permissions.</li>
                                    <li>Login to the application using your user account detials.</li>
                                    <li>Click on the connect to complete the connection.</li>
                                    <li>Now click on the *Admin Connection* button and select your device ID in the opened
                                        box.</li>
                                    <li>After making sure that the ID is correct click on the *Attach Device* button and now
                                        you will see the device in your connected device table.</li>
                                </ol>

                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attachAndroidDevice">Admin Connection</button>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="attachAndroidDevice" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" action="{{ route('admin.gateway.attach_device') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="text-center card-title text--light">
                                    {{ translate('Select device for admin use!') }}</div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="device_id" class="form-label">{{ translate('Device Id')}} <sup class="text--danger">*</sup></label>
                                    <select class="form-select" name="device_id" id="device_id" required>
                                        <option value="">Select a device ID to connect</option>
                                        @foreach ($all_devices as $device)
                                            <option value="{{ $device->id }}">{{ $device->device_id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-success">{{ translate('Attach Device') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
