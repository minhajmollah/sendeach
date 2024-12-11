@extends('admin.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row">
                <div class="col">
                    <div class="card mb-2">
                        <div class="card-header">
                            {{ translate('WhatsApp Desktop Device List') }}
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table dataTable">
                                    <thead>
                                    <tr>
                                        <th>{{ translate('Connected At') }}</th>
                                        <th>{{ translate('Device ID') }}</th>
                                        <th>{{ translate('User') }}</th>
                                        <th>{{ translate('User Type') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($whatsAppDevices as $item)
                                        <tr id="whatsappDevice_{{ $item->id }}">
                                            <td>{{ $item->created_at->toDateTimeString() }}</td>
                                            <td>{{ $item->device_id }}</td>
                                            <td>{{ $item->user?->name }}</td>
                                            <td>{{ $item->user_type }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ strtolower($item->status) == 'initiate' ? 'primary' : (strtolower($item->status) == 'connected' ? 'success' : 'danger') }}">
                                                    {{ ucwords($item->status) }}
                                                </span>
                                            </td>
                                            <td>

                                                <a title="Update" href="#" class="badge bg-primary p-2 update"
                                                   data-value="{{ $item->id }}" data-type="{{ $item->user_type }}"><i class="fas fa-edit"></i></a>
                                                <a title="Delete" href="#" class="badge bg-danger p-2 whatsappDelete"
                                                   data-value="{{ $item->id }}"><i class="fas fa-trash-alt"></i></a>

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
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
                <form action="{{ route('admin.desktop.gateway.whatsapp.delete') }}" method="POST">
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

    <div class="modal fade" id="update" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <label for="type"
                           class="form-label">{{translate('Device Type')}} <sup
                            class="text--danger">*</sup></label>
                </div>
                <form action="{{ route('admin.desktop.gateway.whatsapp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="">
                    <div class="modal_body2">
                        <div class="mb-3">

                            <p class="text-secondary py-2">This Gateway will be responsible for delivering all your OTP messages when set to admin.</p>
                            <select class="form-select" id="type" name="type"
                                    required="">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="bg--primary">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection


@push('scriptpush')
    <script>

        $(document).ready(function () {
            $(".dataTable").dataTable()


            $(document).on('click', '.whatsappDelete', function (e) {
                e.preventDefault()
                var id = $(this).data('value')
                var modal = $('#whatsappDelete');
                modal.find('input[name=id]').val(id);
                modal.modal('show');
            })

            $(document).on('click', '.update', function (e) {
                e.preventDefault()
                let elem = $(this)
                let modal = $('#update');
                modal.find('input[name=id]').val(elem.data('value'));
                modal.find('#type').val(elem.data('type'));
                modal.modal('show');
            })
        })
    </script>
@endpush
