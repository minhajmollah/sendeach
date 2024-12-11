@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
    $routeStr = $routePrefix.(request()->routeIs($routePrefix.'.desktop.*') ? '.desktop.whatsapp.' : '.whatsapp.' );
@endphp

@extends($routePrefix.'.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row gap-2">
                <div class="row justify-content-between align-items-center gap-2 m-0 mb-2 p-0">
                    <div class="d-flex col-lg-4 gap-2 col-sm-12 col-md-6 justify-content-start">
                        <div class="">
                            <button class="btn btn--danger whatsapp_delete"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Delete">{{translate('Delete')}}</button>
                            @if(request()->routeIs('*desktop*'))
                            <button class="btn btn--primary pause"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Pause">{{translate('Pause/UnPause the campaign')}}</button>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-6 col-sm-12 row justify-content-end">
                        @if(@$whatsApp?->first()->gateway == \App\Models\WhatsappLog::GATEWAY_DESKTOP)
                            <div class="col-12 col-lg-6 col-xl-6">
                                <form method="POST" action="{{ route('user.desktop.whatsapp.update-gateway') }}"
                                      class="form-inline float-sm-right text-end">
                                    @csrf
                                    <div class="input-group w-100">
                                        <input type="hidden" name="id" id="gateway_id">
                                        <select class="form-select" name="gateway" required="">
                                            <option selected disabled>Select Whatsapp Gateway</option>
                                            @foreach(\App\Models\UserWindowsToken::query()->where('user_id', auth()->id())
                                        ->where('status', \App\Models\WhatsappDevice::STATUS_CONNECTED)->get() as $gateway)
                                                <option value="{{ $gateway->id }}"
                                                >{{ $gateway->device_id }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn--primary input-group-text input-group-text" id="basic-addon2"
                                                type="submit">@lang('Change Gateway')</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                </div>

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
                                    <th>{{ translate('Batch ID')}}</th>
                                    <th>{{ translate('Batch Started At')}}</th>
                                    <th>{{ translate('Batch Completed At')}}</th>
                                    <th>{{ translate('Status')}}</th>
                                    <th>{{ translate('View')}}</th>
                                </tr>
                                </thead>
                                @forelse($whatsappReports as $whatsappReport)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td class="d-none d-md-flex align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="checkbox" name="batch_id"
                                                   value="{{$whatsappReport->batch_id}}"
                                                   aria-label="Checkbox for following text input">
                                            {{$loop->iteration}}
                                        </td>

                                        <td data-label="{{ translate('Batch ID')}}">
                                            {{$whatsappReport->batch_id}}
                                            @if($whatsappReport->paused)
                                                <span class="badge badge--primary">Paused</span>
                                            @endif
                                        </td>

                                        <td data-label="{{ translate('Batch Started At')}}">
                                            {{\Carbon\Carbon::parse($whatsappReport->started_at)->toFormattedDateString()}}
                                        </td>

                                        <td data-label="{{ translate('Batch Completed At')}}">
                                            {{ !($whatsappReport->pending || $whatsappReport->processing)
                                            ? \Carbon\Carbon::parse($whatsappReport->completed_at)->toFormattedDateString() : '-'}}
                                        </td>

                                        <td data-label="{{ translate('Status')}}">
                                            {{$whatsappReport->delivered}} Delivered Out Of {{ $whatsappReport->total }}
                                        </td>

                                        <td data-label={{ translate('Action')}}>
                                            <a class="btn--primary text--light details campaign-view-btn"
                                               data-message="{{$whatsappReport->message}}"
                                               data-batch-id="{{$whatsappReport->batch_id}}"
                                               data-total="{{$whatsappReport->total}}"
                                               data-pending="{{$whatsappReport->pending}}"
                                               data-processing="{{$whatsappReport->processing}}"
                                               data-delivered="{{$whatsappReport->delivered}}"
                                               data-failed="{{$whatsappReport->failed}}"
                                               data-link="{{route($routeStr.'pending', ['batch_id' => $whatsappReport->batch_id])}}"
                                               data-bs-placement="top" title="Details"
                                               data-bs-toggle="modal"
                                               data-bs-target="#campaign_modal"
                                            ><i class="las la-desktop"></i></a>
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
                            {{ $whatsappReports->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="modal fade" id="campaign_modal" style="z-index: 10000" tabindex="-1" aria-labelledby="Campaign"
         aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-md-down">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="card-title">
                        <div class="text-muted"> Batch ID - <b class="fw-bold" id="batch-id"></b></div>
                    </div>
                    <button type="button" data-bs-dismiss="modal" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="w-100 m-0 row gap-4 justify-content-center">

                            <x-report-card title="Total Messages" id="total" link="#"
                                           icon_class="bg-secondary" link_text="View All">
                                <i class='fab fa-whatsapp fs-3'> </i>
                            </x-report-card>

                            <x-report-card title="Delivered Messages" class="col-12" id="delivered"
                                           link_text="View All" link="#"
                                           icon_class="bg-success" link_text="View All">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                     fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                            </x-report-card>

                            <x-report-card title="Pending Messages" class="col-12" icon_class="bg-info" id="pending"
                                           link_text="View All" link="#">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="24" height="24"
                                     viewBox="0,0,256,256"
                                     style="fill:#000000;">
                                    <g fill="#ffffff" fill-rule="nonzero" stroke="none" stroke-width="1"
                                       stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                       stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                       font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M25,2c-12.683,0 -23,10.317 -23,23c0,12.683 10.317,23 23,23c12.683,0 23,-10.317 23,-23c0,-12.683 -10.317,-23 -23,-23zM25,28c-0.462,0 -0.895,-0.113 -1.286,-0.3l-6.007,6.007c-0.195,0.195 -0.451,0.293 -0.707,0.293c-0.256,0 -0.512,-0.098 -0.707,-0.293c-0.391,-0.391 -0.391,-1.023 0,-1.414l6.007,-6.007c-0.187,-0.391 -0.3,-0.824 -0.3,-1.286c0,-1.304 0.837,-2.403 2,-2.816v-14.184c0,-0.553 0.447,-1 1,-1c0.553,0 1,0.447 1,1v14.184c1.163,0.413 2,1.512 2,2.816c0,1.657 -1.343,3 -3,3z"></path>
                                        </g>
                                    </g>
                                </svg>
                            </x-report-card>

                            <x-report-card title="Processing Messages" class="col-12" id="processing"
                                           link="#" icon_class="bg-primary" link_text="View All">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                    </g>
                                </svg>

                            </x-report-card>

                            <x-report-card title="Failed Messages" class="col-12" id="failed"
                                           link="#" icon_class="bg-danger" link_text="View All">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                     fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                                </svg>
                            </x-report-card>
                        </div>
                    </div>
                    <div class="card-footer p-3">
                        <div class="h6 mb-3">Message:</div>
                        <p class="fs-6 p-2" id="message">
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route($routePrefix.'.whatsapp.delete.campaign') }}"
                      method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete these campaigns permanently ?')}}</h6>
                            <p class="text-muted small">Note: There is no guarantee that pending or processing messages
                                will not be sent, even if their logs are deleted.</p>
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

    <form method="POST" id="pause-form" action="{{ route($routePrefix.'.desktop.whatsapp.pause') }}">
        @method('PUT') @csrf
        <input type="hidden" id="campaign-id" name="campaign_id">
    </form>

@endsection

@pushonce('scriptpush')
    <script>
        let campaignModal = $("#campaign_modal")

        $(".campaign-view-btn").click(function () {
            let data = $(this).data()
            campaignModal.find("#total .report-value").text(data['total'])
            campaignModal.find("#total .link").attr('href', data['link'].replace('pending', 'all'))
            campaignModal.find("#pending .report-value").text(data['pending'])
            campaignModal.find("#pending .link").attr('href', data['link'])
            campaignModal.find("#processing .report-value").text(data['processing'])
            campaignModal.find("#processing .link").attr('href', data['link'].replace('pending', 'processing'))
            campaignModal.find("#failed .report-value").text(data['failed'])
            campaignModal.find("#failed .link").attr('href', data['link'].replace('pending', 'failed'))
            campaignModal.find("#delivered .report-value").text(data['delivered'])
            campaignModal.find("#delivered .link").attr('href', data['link'].replace('pending', 'delivered'))

            campaignModal.find("#message").text(data['message'])
            campaignModal.find("#batch-id").text(data['batchId'])
        })

        $('.checkAll').click(function () {
            $('input:checkbox').not(this).prop('checked', this.checked);

            let ids = [];
            $("input:checkbox[name=batch_id]:checked").each(function () {
                ids.push($(this).val());
            });

            $("#gateway_id").val(ids.join(', '))
        });

        $('.whatsapp_delete').on('click', function () {
            let modal = $('#delete');

            let ids = [];
            $("input:checkbox[name=batch_id]:checked").each(function () {
                ids.push($(this).val());
            });

            let data = $(this).data('delete_id')
            data ? ids.push(data) : null

            if ($(this).data('delete-all')) {
                ids = '{{  \Illuminate\Support\Arr::last(explode('/', request()->path())) }}'
            }

            modal.find('input[name=id]').val(ids);
            modal.modal('show');
        });

        $(".pause").on('click', function () {

            let ids = [];
            $("input:checkbox[name=batch_id]:checked").each(function () {
                ids.push($(this).val());
            });
            
            const pauseForm = $("#pause-form");
            pauseForm.find("#campaign-id").val(ids)
            pauseForm.submit()
        });

    </script>

@endpushonce
