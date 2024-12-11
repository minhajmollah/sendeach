@extends(auth('web')->id() ? 'user.layouts.app' : 'admin.layouts.app')

@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form
                                action="{{route($routePrefix.'.business.whatsapp.search',$scope ?? str_replace($routePrefix.'.business.whatsapp.', '', request()->route()->getName()))}}"
                                method="GET">
                                <div class="row align-items-center">
                                    <div class="col-lg-5">
                                        <label>{{ translate('By User/Email/To Recipient Number')}}</label>
                                        <input type="text" autocomplete="off" name="search"
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

                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th>{{ translate('S.No')}}</th>
                                    @if($routePrefix == 'admin')
                                        <th>{{ translate('User')}}</th>
                                    @endif
                                    <th>{{ translate('Sender')}}</th>
                                    <th>{{ translate('To')}}</th>
                                    <th>{{ translate('Credit ')}}</th>
                                    <th>{{ translate('Initiated')}}</th>
                                    <th>{{ translate('Status')}}</th>
                                </tr>
                                </thead>
                                @forelse($whatsappLogs as $whatsappLog)

                                    <tr class="@if($loop->even) table-light @endif">
                                        <td class="d-none d-md-flex align-items-center">
                                            {{$loop->iteration}}
                                        </td>
                                        @if($routePrefix == 'admin')
                                            <td data-label="{{ translate('User')}}">
                                                @if($whatsappLog->user  )
                                                    <a href="{{route('admin.user.details', $whatsappLog->user_id)}}"
                                                       class="fw-bold text-dark">{{__($whatsappLog->user?->email)}}</a>
                                                @else
                                                    <span>{{ translate('Admin')}}</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td data-label="{{ translate('Sender')}}">

                                            @if(optional($whatsappLog->whatsapp_phone_number)->display_phone_number)
                                                {{ translate('WhatsApp Phone Number')}} <i
                                                    class="las la-arrow-right"></i> <span
                                                    class="text--success fw-bold">{{ucfirst(optional($whatsappLog->whatsapp_phone_number)->verified_name)}}</span>
                                            @endif
                                        </td>

                                        <td data-label="{{ translate('To')}}">
                                            {{$whatsappLog->to}}
                                        </td>

                                        <td data-label="{{ translate('Credit')}}">
                                            {{ $whatsappLog->gateway == \App\Models\WhatsappLog::GATEWAY_BUSINESS && $whatsappLog->user_id ? 1 : 0 }}
                                            {{ translate('Credits')}}
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
                <form action="{{route($routePrefix.'.business.whatsapp.delete')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="">
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


            $('.details').on('click', function () {
                var modal = $('#smsdetails');
                var message = $(this).data('message');
                var response_gateway = $(this).data('response_gateway');
                $("#message--text").text(message + " :: " + response_gateway);
                modal.modal('show');
            });

            $('.smsdelete').on('click', function () {
                var modal = $('#delete');
                modal.find('input[name=id]').val($(this).data('delete_id'));
                modal.modal('show');
            });

            $('.checkAll').click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });


        })(jQuery);
    </script>
@endpush
