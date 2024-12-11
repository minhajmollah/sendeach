@php
    use App\Models\WhatsappTemplate;
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@extends(auth('web')->id() ? 'user.layouts.app' : 'admin.layouts.app')

@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-content-center flex-wrap my-2 gap-2">
                <form method="GET" action="{{ route($routePrefix.'.business.whatsapp.template.index') }}"
                      id="whatsapp_business_account_form">
                    <select name="whatsapp_business_id" id="whatsapp_business_id"
                            class="form-select float-right">
                        @foreach($whatsappBusinessAccounts as $whatsappAccount)
                            <option
                                value="{{ $whatsappAccount->whatsapp_business_id }}"
                                @if($whatsappAccount->whatsapp_business_id == request('whatsapp_business_id')) selected @endif>{{ $whatsappAccount->name }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="alert alert-warning">
                    Only template with Status: <i>APPROVED, REJECTED, PAUSED and ERROR</i> Can be edited.
                </div>
                <div>
                    <a href="{{ route($routePrefix.'.business.whatsapp.template.sync') }}"
                       class="btn btn-primary me-sm-3 me-1 float-end">{{ translate('Sync Templates from Meta Business Accounts')}}</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Template ID')</th>
                                    <th>@lang('Components')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                @forelse($templates as $template)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td data-label="@lang('Name')">
                                            {{$template->name}}
                                        </td>
                                        <td data-label="@lang('Whatsapp Template')">
                                            {{$template->whatsapp_template_id}}
                                        </td>
                                        <td data-label="@lang('Components')" class="row">
                                            <p class="text-wrap col-10">
                                                {{$template->components->where('type', 'BODY')->first()->text}}
                                            </p>
                                            <div class="col-2">
                                                <a class="btn--primary text--light viewComponent" data-bs-toggle="modal"
                                                   data-bs-target="#viewComponent" href="javascript:void(0)"
                                                   data-components="{{$template->components->toJson()}}"><i
                                                        class="las la-eye"></i></a>
                                            </div>
                                        </td>

                                        <td data-label="@lang('Status')">
                                            @if(\Illuminate\Support\Arr::has([WhatsappTemplate::STATUS_PENDING, WhatsappTemplate::STATUS_PENDING_DELETION],
                                                    $template->status))
                                                <div class="badge bg-primary">{{ $template->status  }}</div>
                                            @elseif($template->status == WhatsappTemplate::STATUS_APPROVED)
                                                <div class="badge bg-success">{{ $template->status }}</div>
                                            @elseif($template->status == WhatsappTemplate::STATUS_REJECTED)
                                                <div class="badge bg-danger">{{ $template->status }}</div>
                                            @elseif($template->status == WhatsappTemplate::STATUS_ERROR)
                                                <div class="badge bg-danger">{{ $template->status }}</div>
                                            @else
                                                <div class="badge bg-secondary">{{ $template->status }}</div>
                                            @endif
                                        </td>

                                        <td data-label=@lang('Action')>
                                            <div class="d-flex gap-2">

                                                @if($template->isEditable())
                                                    <a class="btn--primary text--light"
                                                       href="{{route($routePrefix.'.business.whatsapp.template.edit', $template->id)}}">
                                                        <i class="las la-pen"></i>
                                                    </a>
                                                @endif
                                                @if(auth('admin')->check())
                                                    <button href="#"
                                                            class="btn btn-primary toggleTemplatePublic @if($template->is_public) active @endif"
                                                            data-value="{{ $template->whatsapp_template_id }}"
                                                            data-bs-toggle="button" autocomplete="off">
                                                        @if($template->is_public)
                                                            Make Private
                                                        @else
                                                            Make Public
                                                        @endif
                                                        @endif
                                                    </button>

                                                    <a class="btn--danger text--light delete" data-bs-toggle="modal"
                                                       data-bs-target="#deletetemplate" href="javascript:void(0)"
                                                       data-id="{{$template->id}}"><i class="las la-trash"></i></a>
                                            </div>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No Data Found')</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        <div class="m-3">
                            {{$templates->appends(request()->all())->links()}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">Meta Official Business Whatsapp Team will manage Template status.</div>
        </div>
        <a href="{{ route($routePrefix.'.business.whatsapp.template.create') }}" class="support-ticket-float-btn"
           title="@lang('Create New Message Template')">
            <i class="fa fa-plus ticket-float"></i>
        </a>
    </section>

    <div class="modal fade" id="deletetemplate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route($routePrefix.'.business.whatsapp.template.delete', 'ID')}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>@lang('Are you sure to want delete this template?')</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="bg--danger">@lang('Delete')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewComponent" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light"></div>
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>

                <div class="modal_button2">
                    <button type="button" class="" data-bs-dismiss="modal">@lang('Close')</button>
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

            $("#whatsapp_business_id").on('change', function () {
                $('#whatsapp_business_account_form').submit();
            });

            $('.component_input').on('change', function () {
                let inputElement = $(this);

                let vars = inputElement.val().match(/\{\{[0-9]}\}/g);

                if (!vars) {
                    return;
                }

                let variableElem = inputElement.next('.variables');
                let name = inputElement.attr('name');
                let html = '';

                vars.forEach((val, i) => {
                    html += `<input type="text" class="form-control my-2" id="name" name="${name}[vars][]"
                                           placeholder="Example ${val}" required>`
                })

                variableElem.html(html)
            })

            $('.delete').on('click', function () {
                let modal = $('#deletetemplate');
                let form = modal.find('form');
                form.attr('action', form.attr('action').replace('ID', $(this).data('id')));
                modal.modal('show');
            });

            $('.viewComponent').on('click', function () {
                var modal = $('#viewComponent');
                modal.find('.card-body').html(JSON.stringify($(this).data('components'), undefined, 5));
                modal.modal('show');
            });

            @if(auth('admin')->check())

            $(document).on('click', '.toggleTemplatePublic', function (e) {

                let btn = $(this);

                let value = btn.data('value');

                $.ajax({
                    url: '{{ route('admin.business.whatsapp.template.toggle_public') }}',
                    data: {
                        'whatsapp_template_id': value
                    },
                    dataType: "json",
                    type: "POST",
                    async: true,
                    headers: {
                        'X-CSRF-TOKEN': "{{csrf_token()}}",
                    },
                    success: function (response) {
                        console.log(response)

                        if (response.is_public) {
                            btn.text('Make Private')
                        } else {
                            btn.text('Make Public')
                        }
                    },
                    error: function (response) {
                        console.log(response)

                        alert('Error unable to make public')
                    }
                });
            })

            @endif

        })(jQuery);
    </script>
@endpush
