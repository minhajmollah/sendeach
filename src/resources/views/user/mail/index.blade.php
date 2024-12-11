@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="p-0 container-fluid">
            <div class="row">
                <div class="col-xl-6">
                    <form action="{{ route('user.mail.send.method') }}" method="POST" enctype="multipart/form-data">
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
                                            all messages sent from your account, whether sent via our web
                                            interface or our APIs.</p>
                                        <select class="form-select" id="id" name="id" required="">
                                            <option disabled selected>{{ translate('Select Gateway') }}</option>
                                            @foreach ($mails->where('status', \App\Models\MailConfiguration::STATUS_ACTIVE)  as $gateway)
                                                <option value="{{ $gateway->id }}"
                                                    @if ($gateway->default_use) selected @endif>{{ $gateway->name }}
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
                <div class="col-xl-6">
                    <form action="{{ route('user.ticket.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-2">
                            <div class="card-header">
                                {{ translate('Request for increase SendEach  Gateway Mail limit') }}
                            </div>
                            @php
                                $count = 0;
                                $data = \cache()->get('user-email-limits-dfault::' . auth()->user()->id);
                                if ($data) {
                                    $count = $data['count'] ?? 0;
                                }

                            @endphp
                            <div class="card-body">
                                <div class="row">
                                    <b class="text-success col-6">Your SendEach Mail Limit: <strong>200</strong> (Free)</b>
                                    <b class="text-success col-6"> Your Default Mail Limit: <strong>{{ auth()->user()->email_limits }}</strong> </b>
                                    <b class="text-success  col-6">Your Total Mail Limit :
                                        <strong>{{ auth()->user()->default_limit + auth()->user()->email_limits }}</strong></b>
                                    <b class="text-danger col-6"> Total Utilized of SendEach Mails: <strong>{{ $count }}</strong> </b>
                                    <b class="text-primary col-12">You have <strong>{{ auth()->user()->default_limit + auth()->user()->email_limits - $count }}</strong>
                                        SendEach Mails available for use.</b>
                                </div>
                                <div class="row mt-3">
                                    <div class="mb-3 col-md-12">
                                        <textarea name="message" id="message" class="form-control" rows="1"
                                        placeholder="Compose a message specifying the quantity of mail you wish to increase."></textarea>
                                        <input type="hidden" name="subject"
                                            value="Request for Increase SendEach Mail Limit">
                                        <input type="hidden" name="priority" value="3">
                                    </div>
                                    <div class="row">
                                        <button type="submit"
                                            class="btn col-6 m-auto btn-primary me-sm-3 me-1 float-end">{{ translate('Request Limit Increase') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="p-1 col-lg-12">
                    <div class="rounded_box">
                        <div class="responsive-table">
                            <table class="m-0 table--light">
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

                                            <span>{{ __($mail->name) }}</span>
                                            @if ($mail->default_use == 1)
                                                <span class="text--success fs-5">
                                                    <i class="las la-check-double"></i>
                                                </span>
                                            @endif
                                            @if($mail->user_type == 'default')
                                                <span class="badge badge--primary" style="left: 50%;">System</span>
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
                                            @if ($mail->driver_information && $mail->name !== 'SendEach')
                                                <a class="btn--primary text--light"
                                                    href="{{ route('user.mail.edit', $mail->id) }}"><i
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
