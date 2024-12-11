@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col-xl">
                    <div class="card mb-2">
                        <div class="card-header">
                            {{ translate('WhatsApp Business Accounts List')}}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>{{ translate('Name')}}</th>
                                        <th>{{ translate('Whatsapp Business ID')}}</th>
                                        <th>{{ translate('Whatsapp Template Namespaces')}}</th>
                                        <th>{{ translate('Timezone ID')}}</th>
                                        {{-- <th>{{ translate('Multi Device')}}</th> --}}
                                        <th>{{ translate('Action')}}</th>
                                    </tr>
                                    @forelse ($whatsappAccounts as $item)
                                        <tr id="whatsapp_account_{{ $item->whatsapp_business_id }}">
                                            <td style="padding-left: 1rem !important;">{{$item->name}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->whatsapp_business_id}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->message_template_namespace}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->timezone_id}}</td>
                                            <td style="padding-left: 1rem !important;">
                                                <a title="Delete" href="" class="badge bg-danger p-2 whatsappDelete"
                                                   value="{{$item->whatsapp_business_id}}"><i
                                                        class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="50"><span
                                                    class="text-danger">{{ translate('No data Available')}}</span></td>
                                        </tr>
                                    @endforelse
                                </table>
                            </div>
                            <div class="m-3">
                                {{$whatsappAccounts->appends(request()->all())->links()}}
                            </div>
                        </div>
                        <div class="card-footer">
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card mb-2">
                        <div class="card-header">
                            {{ translate('WhatsApp Business Phone Number List')}}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>{{ translate('Name')}}</th>
                                        <th>{{ translate('Number')}}</th>
                                        <th>{{ translate('Whatsapp Business ID')}}</th>
                                        <th>{{ translate('Whatsapp Number ID')}}</th>
                                        <th>{{ translate('Status')}}</th>
                                        <th>{{ translate('Action')}}</th>
                                    </tr>
                                    @forelse ($whatsappNumbers as $item)
                                        <tr id="whatsappDevice_{{ $item->whatsapp_phone_number_id }}">
                                            <td style="padding-left: 1rem !important;">{{ucfirst($item->verified_name)}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->display_phone_number}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->whatsapp_business_id}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->whatsapp_phone_number_id}}</td>
                                            <td style="padding-left: 1rem !important;">{{$item->code_verification_status}}</td>
                                            <td style="padding-left: 1rem !important;">
                                                <button href="#" class="btn btn-primary makePhonePublic @if($item->is_public) active @endif"
                                                        data-value="{{ $item->whatsapp_phone_number_id }}" data-bs-toggle="button" autocomplete="off">
                                                    @if($item->is_public) Make Private @else Make Public @endif
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="50"><span
                                                    class="text-danger">{{ translate('No data Available')}}</span></td>
                                        </tr>
                                    @endforelse
                                </table>
                            </div>
                            <div class="m-3">
                                {{$whatsappNumbers->appends(request()->all())->links()}}
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.business.whatsapp.account.sync') }}"
                               class="btn btn-primary me-sm-3 me-1 float-end">{{ translate('Sync Whatsapp Numbers, Message Templates from Meta Business Account')}}</a>
                        </div>
                    </div>
                </div>
{{--                <div class="col-12 mt-5">--}}
{{--                    <div class="alert alert-primary col-12">Please set how much credits need to be deducted per message when user sends using Whatsapp Business Gateway.</div>--}}
{{--                    <form method="POST" action="{{ route('admin.business.whatsapp.account.update_rate') }}"--}}
{{--                          class="col-12 row justify-content-evenly align-items-center">--}}
{{--                        @php $i = 0; @endphp--}}
{{--                        @foreach($whatsappRates as $key => $rates)--}}
{{--                            <div class="col">--}}
{{--                                <div class="card mb-2">--}}
{{--                                    <div class="card-header">--}}
{{--                                        {{ translate('Credits Per Each Message for '.$key.' Users')}}--}}
{{--                                    </div>--}}
{{--                                    <div class="card-body">--}}
{{--                                        <div class="row justify-content-evenly align-items-center">--}}
{{--                                            @csrf--}}
{{--                                            @foreach($rates as $rate)--}}
{{--                                                <div class="col-lg-3">--}}
{{--                                                    <label for="credits">{{ $rate->category.' '.translate('Credits') }}--}}
{{--                                                        <span--}}
{{--                                                            class="text-danger">*</span></label>--}}
{{--                                                    <input type="hidden" value="{{$rate->id}}"--}}
{{--                                                           name="rate[{{ $i + $loop->index }}][id]">--}}
{{--                                                    <input type="number"--}}
{{--                                                           class="form-control credits"--}}
{{--                                                           name="rate[{{ $i + $loop->index }}][credits]" id="credits"--}}
{{--                                                           value="{{ $rate->credits }}"--}}
{{--                                                           placeholder="{{ translate('Credits')}}" disabled>--}}
{{--                                                </div>--}}
{{--                                            @endforeach--}}
{{--                                            @php $i += count($rates); @endphp--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="card-footer">--}}

{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
{{--                        <div class="row justify-content-center">--}}
{{--                            <div class="col">--}}
{{--                                <button type="button" class="btn btn-primary m-auto"--}}
{{--                                        onclick="enableEdit()"><i class="fas fa-pencil me-2"></i>Edit--}}
{{--                                </button>--}}
{{--                            </div>--}}

{{--                            <div class="col">--}}
{{--                                <button type="submit" id="updateCredit" class="btn btn-primary"--}}
{{--                                        style="display: none; width: 100px; margin: auto;">Update--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </form>--}}
{{--                </div>--}}
            </div>
        </div>
    </section>

    {{-- whatsapp delete modal --}}
    <div class="modal fade" id="whatsappDelete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('admin.business.whatsapp.account.delete')}}" method="POST">
                    @csrf
                    @method('delete')
                    <input type="hidden" name="whatsapp_business_id" value="">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to delete')}}</h6>
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
            $(document).on('click', '.whatsappDelete', function (e) {
                e.preventDefault()
                var id = $(this).attr('value')
                var modal = $('#whatsappDelete');
                modal.find('input[name=whatsapp_business_id]').val(id);
                modal.modal('show');
            })

            $(document).on('click', '.makePhonePublic', function (e) {

                let btn = $(this);

                let value = btn.data('value');

                $.ajax({
                    url: '{{ route('admin.business.whatsapp.phone.toggle_public') }}',
                    data: {
                        'phoneNumberId' : value
                    },
                    dataType: "json",
                    type: "POST",
                    async: true,
                    headers: {
                        'X-CSRF-TOKEN': "{{csrf_token()}}",
                    },
                    success: function (response) {
                        console.log(response)

                        if(response.is_public){
                            btn.text('Make Private')
                        }else{
                            btn.text('Make Public')
                        }
                    },
                    error: function (response) {
                        console.log(response)

                        alert('Error unable to make public')
                    }
                });
            })
        })(jQuery);

        function enableEdit() {
            $('.credits').prop('disabled', false);
            $('#updateCredit').show();
        }
    </script>
@endpush

