@extends('user.layouts.app')
@push('scriptpush')
    <style>
        .select2-container {
            min-width: 100%;
        }

        #select2-group-results {
            min-width: 100%;
        }
    </style>
@endpush
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row d-flex align--center rounded">
                @if($availableDepositAmount < 3 && $availableDepositAmount > 0)
                    <div class="col-12 alert alert-warning">
                        You deposit balance is ${{ $availableDepositAmount }} ({{ $availableCredits }} Credits
                        Remaining). Watermarks will be added if credits goes to 0!.
                        <a href="{{ route('user.credits.create') }}">Buy Credits</a>
                    </div>
                @elseif(!$availableDepositAmount)
                    <div class="col-12 alert alert-warning">
                        Your Messages are sent with Watermarks. Please buy some credits to avoid watermarks. <a
                            href="{{ route('user.credits.create') }}">Buy Credits</a>
                    </div>
                @endif
                <div class="col-12 alert alert-warning">
                    You can send up to 50 WhatsApp messages per sending session using the web gateway. If you need to send more than 50 messages in a single session, kindly utilize the PC Gateway.
                </div>
                <div class="col-xl-12">
                    <div class="col-xl">
                        <form action="{{ route('user.whatsapp.store') }}" method="POST" id="compose-message-form" enctype="multipart/form-data">
                            @csrf

                            <x-compose-whatsapp-message :groups="$groups"
                                                        :devices="$devices->map(function($device){ $device->name =
                                                $device->number; return $device; })"
                                                        :templates="$templates">
                            </x-compose-whatsapp-message>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="templatedata" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light">{{ translate('SMS Template') }}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="template" class="form-label">{{ translate('Select Template') }} <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" name="template" id="template" required>
                                    <option value="" disabled="" selected="">{{ translate('Select One') }}
                                    </option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->message }}">{{ __($template->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
