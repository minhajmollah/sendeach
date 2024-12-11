@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends($routePrefix.'.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">

                <script src="/assets/qrcode.min.js"></script>

                @if($routePrefix == 'user')
                    @include($routePrefix.'.partials.whatsapp-default-settings-select')
                @endif

                <div class="col-xl-4">
                    <form action="{{ route($routePrefix.'.gateway.whatsapp.create') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('Add Whatsapp Device') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <label for="name">{{ translate('Name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror "
                                               name="name" id="name" value="{{ old('name') }}"
                                               placeholder="{{ translate('Put Session Name (Any)') }}">
                                        @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label for="number">{{ translate('Number') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('number') is-invalid @enderror "
                                               name="number" id="number" value="{{ old('number') }}"
                                               placeholder="{{ translate('Put Your WhatsApp number here') }}">
                                        @error('number')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    {{--                                    <div class="col-md-12 mb-4">--}}
                                    {{--                                        <label for="description">{{ translate('Description') }}</label>--}}
                                    {{--                                        <textarea name="description" id="description" class="form-control"--}}
                                    {{--                                                  placeholder="{{ translate('Remark Your WhatsApp device(any)') }}">{{ old('description') }}</textarea>--}}
                                    {{--                                    </div>--}}
                                    <div class="col-md-12 mb-4">
                                        <label for="delay_time">{{ translate('Message Delay Time') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="number"
                                               class="form-control @error('delay_time') is-invalid @enderror "
                                               name="delay_time" id="delay_time" value="{{ old('delay_time') }}"
                                               placeholder="{{ translate('Message delay time in second') }}">
                                        @error('delay_time')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            Your Whatsapp account will be blocked from <strong>WhatsApp</strong> in case
                                            you
                                            are sending SPAM messages to users. SendEach doesn't hold any reponsibiliy
                                            on
                                            how you use our service and will not be liallble to any damages you incur by
                                            using our service.
                                        </div>
                                        <label class="mb-4">
                                            <input type="checkbox" required>
                                            I agree that, SendEach will not responsible if my WhatsApp gets blocked.
                                        </label>
                                    </div>
                                </div>
                                <button type="submit"
                                        class="btn btn-primary me-sm-3 me-1 float-end">{{ translate('Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-8">
                    <div class="card mb-2">
                        <div class="card-header">
                            {{ translate('WhatsApp Device List') }}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>{{ translate('Name') }}</th>
                                        @if($routePrefix == 'admin')
                                            <th>{{ translate('User')}}</th>
                                        @endif
                                        <th>{{ translate('Number') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Action') }}</th>
                                    </tr>
                                    @forelse ($whatsAppDevices as $item)
                                        <tr id="whatsappDevice_{{ $item->id }}">
                                            <td>{{ $item->name }}</td>
                                            @if($routePrefix == 'admin')
                                                <td>{{ $item->user?->name ?: 'admin' }}</td>
                                            @endif
                                            <td>{{ $item->number }}</td>
                                            {{--                                            <td>{{ $item->description }}</td>--}}
                                            <td>
                                                <span
                                                    class="badge bg-{{ $item->status == 'initiate' ? 'primary' : ($item->status == 'connected' ? 'success' : 'danger') }}">
                                                    {{ ucwords($item->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($item->status == 'initiate')
                                                    <a title="Scan" href="javascript:void(0)"
                                                       class="badge bg-success p-2 qrQuote textChange{{ $item->id }}"
                                                       data-value="{{ $item->id }}"><i class="fas fa-qrcode"></i>&nbsp
                                                        {{ translate('Scan') }}</a>
                                                @elseif($item->status == 'connected')
                                                    <a title="Disconnect"
                                                       href="{{ route($routePrefix.'.gateway.whatsapp.disconnect', ['id' => $item->id]) }}"
                                                       class="badge bg-danger p-2"><i class="fas fa-plug"></i>&nbsp
                                                        {{ translate('Disconnect') }}</a>
                                                @elseif($item->status == \App\Models\WhatsappDevice::STATUS_DISCONNECTED)
                                                    <a title="Scan" href="javascript:void(0)"
                                                       class="badge bg-success p-2 qrQuote textChange{{ $item->id }}"
                                                       data-value="{{ $item->id }}"><i class="fas fa-qrcode"></i>&nbsp
                                                        {{ translate('Scan') }}</a>
                                                @endif

                                                <a title="Edit"
                                                   href="{{ route($routePrefix.'.gateway.whatsapp.edit', $item->id) }}"
                                                   class="badge bg-primary p-2"><i class="fas fa-pen"></i></a>

                                                <a title="Delete" href="" class="badge bg-danger p-2 whatsappDelete"
                                                   value="{{ $item->id }}"><i class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="50"><span
                                                    class="text-danger">{{ translate('No data Available') }}</span></td>
                                        </tr>
                                    @endforelse
                                </table>
                            </div>
                            <div class="m-3">
                                {{ $whatsAppDevices->appends(request()->all())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- whatsapp delete modal --}}
    <div class="modal fade" id="whatsappDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route($routePrefix.'.gateway.whatsapp.delete') }}" method="POST">
                    @csrf
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

    {{-- whatsapp qrQoute scan --}}
    <div class="modal fade" id="qrQuoteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">{{ translate('Scan Device') }}</h5>
                    <a type="button" id="scan-close"
                       href="{{ route($routePrefix.'.gateway.whatsapp.disconnect', ['id' => '__ID__']) }}"
                       class="btn-close" aria-label="Close"></a>
                </div>
                <div class="modal-body">
                    <div>
                        <h4 class="py-3">{{ translate('To use WhatsApp') }}</h4>
                        <ul>
                            <li>{{ translate('1.Open WhatsApp on your phone') }}</li>
                            <li>{{ translate('2.Tap Menu  or Settings  and select Linked Devices') }}</li>
                            <li>{{ translate('3.Point your phone to this screen to capture the code') }}</li>
                        </ul>
                    </div>
                    <div class="mt-3 text-center">
                        <div style="height: 200px; width: 200px" class="m-auto" id="qrcode"></div>
                        <img style="height: 200px; width: 200px; display:none" class="m-auto" id="qr-success">
                        <h6 class="h5 mt-2" id="qr_code_status">Loading QR Code</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scriptpush')
    <script>
        (function ($) {
            "use strict";
            $(document).ready(function () {
                const popupScanBtn = "{{ $popup_scan_btn_id ?? '' }}";
                if (popupScanBtn) {
                    $(`#${popupScanBtn} .qrQuote`).trigger('click');
                }
            });

            $(document).on('click', '.whatsappDelete', function (e) {
                e.preventDefault()
                var id = $(this).attr('value')
                var modal = $('#whatsappDelete');
                modal.find('input[name=id]').val(id);
                modal.modal('show');
            })

            // qrQuote scan
            $(document).on('click', '.qrQuote', function (e) {
                e.preventDefault()
                scan($(this).data('value'), this)
            })

        })(jQuery);

        const qrCode = new QRCode(document.getElementById("qrcode"))
        const qrQuoteModal = $('#qrQuoteModal');
        const qrSuccess = $('#qr-success');
        const qrCodeElem = $("#qrcode")
        const scanClose = $("#scan-close")

        function scan(id, e) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                url: '{{ route($routePrefix.'.gateway.whatsapp.qrcode', ['id' => '__ID__']) }}'.replace('__ID__', id),
                dataType: 'json',
                method: 'POST',
                beforeSend: function () {
                    $('.textChange' + id).html(`{{ translate('Loading...') }}`);
                },
                success: function (res) {
                    console.log(res)

                    scanClose.attr('href', scanClose.attr('href').replace('__ID__', res.id));

                    if (res.status === '{{\App\Models\WhatsappDevice::STATUS_INITIATE}}' && res.qr) {
                        qrCodeElem.show();
                        qrCode.clear()
                        qrCode.makeCode(res.qr)
                        qrQuoteModal.modal('show');
                        $("#qr_code_status").html('Scan Now')

                        sleep(2500).then(() => {
                            updateQR(id)
                        });
                    } else if (res.status === '{{ \App\Models\WhatsappDevice::STATUS_INITIATE }}') {
                        qrSuccess.attr('src', '{{ asset('assets/dashboard/image/connecting.gif') }}');
                        qrCodeElem.hide();
                        qrSuccess.show()
                        qrQuoteModal.modal('show');
                        $("#qr_code_status").html('Loading QR Code')

                        sleep(1000).then(() => {
                            updateQR(id)
                        });
                    }
                }
            })
        }

        function updateQR(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                url: '{{ route($routePrefix.'.gateway.whatsapp.qrcode', ['id' => '__ID__']) }}'.replace('__ID__', id),
                data: {
                    id: id
                },
                dataType: 'json',
                method: 'POST',
                success: function (res) {

                    console.log(res)

                    scanClose.attr('href', scanClose.attr('href').replace('__ID__', res.id));

                    if (res.status === '{{ \App\Models\WhatsappDevice::STATUS_INITIATE }}' && res.qr) {
                        qrCode.clear()
                        qrCode.makeCode(res.qr)
                        qrCodeElem.show()
                        qrSuccess.hide()
                        $("#qr_code_status").html('Scan Now')

                    } else if (res.status === '{{ \App\Models\WhatsappDevice::STATUS_CONNECTED }}') {

                        qrSuccess.attr('src', '{{ asset('assets/dashboard/image/done.gif') }}');
                        qrCodeElem.hide();
                        qrSuccess.show()

                        sleep(2500).then(() => {
                            qrQuoteModal.modal('hide');
                            location.reload();
                        });
                        $("#qr_code_status").html('Connected Successfully')

                    } else if (res.data?.dataType === 'loading_screen') {
                        qrSuccess.attr('src', '{{ asset('assets/dashboard/image/connecting.gif') }}');
                        qrCodeElem.hide();
                        qrSuccess.show()
                        $("#qr_code_status").html('Loading Chats')
                    }

                    sleep(2500).then(() => {
                        updateQR(id)
                    });
                },
                error: function (res) {
                    qrQuoteModal.modal('hide');
                    location.reload();
                }
            })
        }
    </script>
@endpush
