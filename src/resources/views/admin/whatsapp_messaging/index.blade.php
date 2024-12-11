@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form
                                action="{{route('admin.whatsapp.search',$scope ?? str_replace('admin.whatsapp.', '', request()->route()->getName()))}}"
                                method="GET">
                                <div class="row align-items-center">
                                    <div class="col-lg-5">
                                        <label>{{ translate('By User/Email/To Recipient Number')}}</label>
                                        <input type="text" autocomplete="off" name="search"
                                               value="{{ request('search') }}"
                                               placeholder="{{ translate('Search with User, Email or To Recipient number')}}"
                                               class="form-control" id="search" value="{{@$search}}">
                                    </div>
                                    <div class="col-lg-5">
                                        <label>{{ translate('By Date')}}</label>
                                        <input type="text" class="form-control datepicker-here" name="date"
                                               value="{{@$searchDate}}" data-range="true"
                                               data-multiple-dates-separator=" - " data-language="en"
                                               data-position="bottom right" autocomplete="off"
                                               placeholder="{{ translate('From Date-To Date')}}" id="date">
                                    </div>
                                    <div class="col-lg-2">
                                        <button class="btn btn--primary w-100 h-45 mt-4" type="submit">
                                            <i class="fas fa-search"></i> {{ translate('Search')}}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-start gap-2">
                <button class="btn btn-primary d-none d-md-block statusupdate"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Status Update"
                        data-bs-toggle="modal"
                        data-bs-target="#smsstatusupdate">{{translate('Status Update')}}</button>
                <button class="btn btn-danger d-none d-md-block whatsapp_delete"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Delete">{{translate('Delete')}}</button>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th class="d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-2 checkAll"
                                               type="checkbox"
                                               value=""
                                               aria-label="Checkbox for following text input"> {{ translate('#')}}
                                    </th>
                                    <th>{{ translate('User')}}</th>
                                    <th>{{ translate('Sender')}}</th>
                                    <th>{{ translate('To')}}</th>
                                    <th>{{ translate('Initiated')}}</th>
                                    <th>{{ translate('Status')}}</th>
                                    <th>{{ translate('Action')}}</th>
                                </tr>
                                </thead>
                                @forelse($whatsappLogs as $whatsappLog)

                                    <tr class="@if($loop->even) table-light @endif">
                                        <td class="d-none d-md-flex align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="checkbox" name="whatsappid"
                                                   value="{{$whatsappLog->id}}"
                                                   aria-label="Checkbox for following text input">
                                            {{$loop->iteration}}
                                        </td>

                                        <td data-label="{{ translate('User')}}">
                                            @if($whatsappLog->user_id)
                                                <a href="{{route('admin.user.details', $whatsappLog->user_id)}}"
                                                   class="fw-bold text-dark">{{__($whatsappLog->user?->email)}}</a>
                                            @else
                                                <span>{{ translate('Admin')}}</span>
                                            @endif
                                        </td>


                                        <td data-label="{{ translate('Sender')}}">

                                            @if($whatsappLog->gateway)
                                                {{ \App\Models\WhatsappLog::GATEWAY[$whatsappLog->gateway]}} <i
                                                    class="las la-arrow-right"></i>
                                                <span
                                                    class="text--success fw-bold">{{$whatsappLog->getGatewayName()}}</span>
                                            @endif
                                        </td>

                                        <td data-label="{{ translate('To')}}">
                                            {{$whatsappLog->to}}
                                        </td>

                                        <td data-label="{{ translate('Initiated')}}">
                                            {{getDateTime($whatsappLog->initiated_time)}}
                                        </td>

                                        <td data-label="{{ translate('Status')}}">
                                            @if($whatsappLog->status == 1)
                                                <span class="badge badge--primary">{{ translate('Pending ')}}</span>
                                            @elseif($whatsappLog->status == 2)
                                                <span class="badge badge--info">{{ translate('Schedule')}}</span>
                                            @elseif($whatsappLog->status == 3)
                                                <span class="badge badge--danger">{{ translate('Fail')}}</span>
                                            @elseif($whatsappLog->status == 4)
                                                <span class="badge badge--success">{{ translate('Delivered')}}</span>
                                            @elseif($whatsappLog->status == 5)
                                                <span class="badge badge--primary">{{ translate('Processing')}}</span>
                                            @endif
                                            <a class="s_btn--coral text--light statusupdate"
                                               data-id="{{$whatsappLog->id}}"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top" title="Status Update"
                                               data-bs-toggle="modal"
                                               data-bs-target="#smsstatusupdate"
                                            ><i class="las la-info-circle"></i></a>
                                        </td>

                                        <td data-label={{ translate('Action')}}>
                                            <a class="btn--primary text--light details"
                                               data-message="{{$whatsappLog->message}}"
                                               data-response_gateway="{{$whatsappLog->response_gateway}}"
                                               data-bs-placement="top" title="Details"
                                               data-bs-toggle="modal"
                                               data-bs-target="#smsdetails"
                                            ><i class="las la-desktop"></i></a>

                                            <a href="javascript:void(0)" class="btn--danger text--light whatsapp_delete"
                                               data-bs-toggle="modal"
                                               data-bs-target="#delete"
                                               data-delete_id="{{$whatsappLog->id}}"
                                            ><i class="las la-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center"
                                            colspan="100%">{{ translate('No Data Found')}}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{$whatsappLogs->appends(request()->all())->links()}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <div class="modal fade" id="smsstatusupdate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('admin.whatsapp.status.update')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <input type="hidden" name="smslogid">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div
                                    class="card-title text-center text--light">{{ translate('WhatsApp Status Update')}}</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ translate('Status')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option value="" selected=""
                                                disabled="">{{ translate('Select Status')}}</option>
                                        <option value="1">{{ translate('Pending')}}</option>
                                        <option value="4">{{ translate('Success')}}</option>
                                        <option value="3">{{ translate('Fail')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="bg--success">{{ translate('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="smsdetails" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light">{{ translate('Message')}}</div>
                        </div>
                        <div class="card-body mb-3">
                            <p id="message--text"></p>
                        </div>
                    </div>
                </div>

                <div class="modal_button2">
                    <button type="button" class="w-100" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('admin.whatsapp.delete')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete this message from log')}}</h6>
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
@endsection


@push('scriptpush')
    <script>
        (function ($) {
            "use strict";
            $('.statusupdate').on('click', function () {
                var modal = $('#smsstatusupdate');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });

            $('.details').on('click', function () {
                var modal = $('#smsdetails');
                var message = $(this).data('message');
                var response_gateway = $(this).data('response_gateway');
                $("#message--text").text(message + " :: " + response_gateway);
                modal.modal('show');
            });

            $('.whatsapp_delete').on('click', function () {
                let modal = $('#delete');

                let ids = [];
                $("input:checkbox[name=whatsappid]:checked").each(function () {
                    ids.push($(this).val());
                });

                let data = $(this).data('delete_id')
                data ? ids.push(data) : null

                modal.find('input[name=id]').val(ids);
                modal.modal('show');
            });

            $('.checkAll').click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            $('.statusupdate').on('click', function () {
                var modal = $('#smsstatusupdate');
                var newArray = [];
                $("input:checkbox[name=whatsappid]:checked").each(function () {
                    newArray.push($(this).val());
                });
                modal.find('input[name=smslogid]').val(newArray.join(','));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
