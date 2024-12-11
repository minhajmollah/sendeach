@extends('user.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                @include('user.partials.whatsapp-default-settings-select')
                <div class="col-xl-8">
                    <div class="card mb-2">
                        <div class="card-header">
                            {{ translate('WhatsApp Desktop Device List') }}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>{{ translate('Connected At') }}</th>
                                        <th>{{ translate('Device ID') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Action') }}</th>
                                    </tr>
                                    @forelse ($whatsAppDevices as $item)
                                        <tr id="whatsappDevice_{{ $item->id }}">
                                            <td>{{ $item->created_at->toDateTimeString() }}</td>
                                            <td>{{ $item->device_id }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ strtolower($item->status) == 'initiate' ? 'primary' : (strtolower($item->status) == 'connected' ? 'success' : 'danger') }}">
                                                    {{ ucwords($item->status) }}
                                                </span>
                                            </td>
                                            <td>
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
                <form action="{{ route('user.desktop.gateway.whatsapp.delete') }}" method="POST">
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


@push('scriptpush')
    <script>
        (function ($) {
            "use strict";

            $(document).on('click', '.whatsappDelete', function (e) {
                e.preventDefault()
                var id = $(this).attr('value')
                var modal = $('#whatsappDelete');
                modal.find('input[name=id]').val(id);
                modal.modal('show');
            })

        })(jQuery);

    </script>
@endpush
