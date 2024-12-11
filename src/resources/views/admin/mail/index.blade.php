@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="p-0 container-fluid">
            <div class="row">
                <div class="col-xl-6">
                    <form action="{{ route('admin.mail.send.method') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('Choose Email Default Gateway') }}
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label for="id" class="form-label">{{ translate('Email Default Gateway') }}
                                            <sup class="text--danger">*</sup></label>
                                        <p class="text-secondary py-2">This Gateway will be responsible for delivering
                                            User Login OTP or any User Alert messages sent.</p>
                                        <select class="form-select" id="id" name="id" required="">
                                            <option disabled selected>{{ translate('Select Gateway') }}</option>

                                            @php
                                                $selectedValue = null;
                                            @endphp

                                            @foreach ($mails->where('status', \App\Models\MailConfiguration::STATUS_ACTIVE) as $gateway)
                                                @if ($gateway->user_type != 'default')
                                                    @if ($gateway->default_use == 1)
                                                        @php
                                                            $selectedValue = 1;
                                                        @endphp
                                                    @elseif ($gateway->default_use == 2 && $selectedValue !== 1)
                                                        @php
                                                            $selectedValue = 2;
                                                        @endphp
                                                    @endif

                                                    <option value="{{ $gateway->id }}"
                                                        @if ($gateway->default_use === $selectedValue) selected @endif>
                                                        {{ $gateway->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="row">
                                        <button type="submit"
                                            class="btn col-6 m-auto btn-primary me-sm-3 me-1 float-end">{{ translate('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xl-6">
                    <form action="{{ route('admin.mail.send.method') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('SendEach Default Gateway') }}
                            </div>


                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label for="id"
                                            class="form-label">{{ translate('Email SendEach Gateway for user') }}
                                            <sup class="text--danger">*</sup></label>
                                        <p class="text-secondary py-2">This Gateway will be responsible for using
                                            SendEach gateway mail limit for user .</p>
                                        <input type="hidden" name="sendeach" value="sendeach">
                                        <select class="form-select" id="id" name="id" required="">
                                            <option disabled selected>{{ translate('Select Gateway') }}</option>
                                            @foreach ($mails as $gateway)
                                                @if ($gateway->user_type != 'default' && $gateway->status !== 2)
                                                    <option value="{{ $gateway->id }}"
                                                        @if ($gateway->default_use == 2) selected @endif>
                                                        {{ $gateway->name }}
                                                @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row">
                                        <button type="submit"
                                            class="btn col-6 m-auto btn-primary me-sm-3 me-1 float-end">{{ translate('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="p-1 col-lg-12">
                    <div class="rounded_box">
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                    <tr>
                                        <th> {{ translate('Name') }}</th>
                                        <th> {{ translate('Status') }}</th>
                                        <th> {{ translate('Action') }}</th>
                                    </tr>
                                </thead>
                                @forelse($mails as $mail)
                                    <tr class="@if ($loop->even) table-light @endif">
                                        <td data-label=" {{ translate('Name') }}">
                                            {{ __($mail->name) }}
                                            @if ($mail->default_use == 1 && $mail->user_type != 'default')
                                                <span class="text--success fs-5">
                                                    <i class="las la-check-double"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td data-label=" {{ translate('Status') }}">
                                            @if ($mail->status == 1)
                                                <span class="badge badge--success"> {{ translate('Active') }}</span>
                                            @else
                                                <span class="badge badge--danger"> {{ translate('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td data-label={{ translate('Action') }}>
                                            @if ($mail->driver_information && $mail->user_type != 'default')
                                                <a class="btn--primary text--light"
                                                    href="{{ route('admin.mail.edit', $mail->id) }}"><i
                                                        class="las la-pen"></i></a>
                                            @else
                                                <span> {{ translate('N/A') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted" colspan="100%"> {{ translate('No Data Found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
