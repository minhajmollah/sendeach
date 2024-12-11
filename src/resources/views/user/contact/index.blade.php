@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12 m-1 alert alert-warning">
                    When an Excel sheet has multiple columns for contact numbers, the system will prioritize the last
                    valid column for importing into the group. If you want to import additional contact columns, you'll
                    need to upload the Excel sheet again, making sure to exclude the previously uploaded contact column.
                </div>
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form
                                action="{{ route('user.phone.book.contact.index') }}"
                                method="GET">
                                <div class="row align-items-center">
                                    <div class="col-lg-5">
                                        <input type="text" autocomplete="off" name="search"
                                               placeholder="{{ translate('Search with contacts Number, Name') }}"
                                               class="form-control" id="search" value="{{request('search')}}">
                                    </div>
                                    <div class="col-lg-2">
                                        <button class="btn btn--primary w-100 h-45" type="submit">
                                            <i class="fas fa-search"></i> {{ translate('Search')}}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 p-1">
                    <div class="rounded_box">
                        <div class="row align--center px-2">
                            <div class="col-12 col-md-4 col-lg-4 col-xl-5">
                                <h6 class="my-3">{{ translate('Select SMS Contact Options')}}</h6>
                            </div>
                            @if(!isset($group) || $group->type != \App\Models\Group::TYPE_SYSTEM)
                            <div class="col-12 col-md-8 col-lg-8 col-xl-7">
                                <div class="row justify-content-end">
                                    {{--                                <div class="col-12 col-md-4 col-lg-4 col-xl-3 py-1">--}}
                                    {{--                                    <a href="#" id="toggleStatus" class="w-100 d-block btn--info text--light border-0 px-1 py-2 rounded ms-2"><i class="bi bi-toggles"></i> {{ translate('Subscribe/Unsubscribe')}}</a>--}}
                                    {{--                                </div>--}}
                                    <div class="col-12 col-md-4 col-lg-4 col-xl-3 py-1">
                                        <button class="btn--primary text--light border-0 w-100 px-1 py-2 rounded ms-2"
                                                data-bs-toggle="modal" data-bs-target="#creategroup"><i
                                                class="las la-plus"></i> {{ translate('Add New Contact')}}</button>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-4 col-xl-3 py-1">
                                        <button class="w-100 btn--coral text--light border-0 px-1 py-2 rounded ms-2"
                                                data-bs-toggle="modal" data-bs-target="#contactImport"><i
                                                class="las la-upload"></i> {{ translate('Import Contact')}}</button>
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-4 col-xl-3 py-1">
                                        @if(@$group)
                                            <a href="{{route('user.phone.book.contact.group.export', $group->id)}}"
                                               class="w-100 d-block btn--warning text--light border-0 px-1 py-2 rounded ms-2"><i
                                                    class="las la-cloud-download-alt"></i> {{ translate('Export Contact')}}
                                            </a>
                                        @else
                                            <a href="{{route('user.phone.book.contact.export')}}"
                                               class="w-100 d-block btn--warning text--light border-0 px-1 py-2 rounded ms-2"><i
                                                    class="las la-cloud-download-alt"></i> {{ translate('Export Contact')}}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th>{{ translate('#')}}</th>
                                    <th>{{ translate('Group')}}</th>
                                    <th>{{ translate('Contact#')}}</th>
                                    <th>{{ translate('Name')}}</th>
                                    @if(!isset($group) || $group->type != \App\Models\Group::TYPE_SYSTEM)
                                    <th>{{ translate('Status')}}</th>
                                    @endif
                                    <th>{{ translate('Action')}}</th>
                                </tr>
                                </thead>
                                @forelse($contacts as $contact)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td data-label="{{ translate('#')}}">
                                            {{--                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="contact_id" value="{{$contact->id}}" aria-label="Checkbox for following text input">--}}
                                            {{$loop->iteration}}
                                        </td>

                                        <td data-label="{{ translate('Group')}}">
                                            {{__($contact->group->name)}}
                                        </td>

                                        <td data-label="{{ translate('Contact#')}}">
                                            {{__($contact->contact_no)}}
                                        </td>

                                        <td data-label="{{ translate('Name')}}">
                                            {{__($contact->name)}}
                                        </td>

                                        @if(!isset($group) || $group->type != \App\Models\Group::TYPE_SYSTEM)
                                        <td data-label="{{ translate('Status')}}">
                                            @if($contact->status == \App\Models\Contact::ACTIVE)
                                                <span class="badge badge--success">{{ translate('Subscribed')}}</span>
                                            @else
                                                <span class="badge badge--danger">{{ translate('UnSubscribed')}}</span>
                                            @endif
                                        </td>
                                        @endif

                                        <td data-label={{ translate('Action')}}>
                                            <a class="btn--primary text--light contact" data-bs-toggle="modal"
                                               data-bs-target="#updatebrand" href="javascript:void(0)"
                                               data-id="{{$contact->id}}"
                                               data-group_id="{{$contact->group_id}}"
                                               data-contact_no="{{$contact->contact_no}}"
                                               data-name="{{$contact->name}}"
                                               data-status="{{$contact->status}}"><i class="las la-pen"></i></a>
                                            <a class="btn--danger text--light delete" data-bs-toggle="modal"
                                               data-bs-target="#delete" href="javascript:void(0)"
                                               data-id="{{$contact->id}}"><i class="las la-trash"></i></a>
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
                            {{$contacts->appends(request()->all())->links()}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <div class="modal fade" id="creategroup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.phone.book.contact.store')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="card-title text-center text--light">{{ translate('Add New Contact')}}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <x-phone-input id="contact_no" name="contact_no" class="w-100" countryCode="country_code"></x-phone-input>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ translate('Name')}} <sup
                                            class="text--danger">*</sup></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           placeholder="{{ translate('Enter Name')}}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="group_id" class="form-label">{{ translate('Group')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="group_id" id="group_id" required>
                                        <option value="">{{ translate('Select Group')}}</option>
                                        @foreach($user->group()->whereNot('type', \App\Models\Group::TYPE_SYSTEM)->get() as $group)
                                            <option value="{{$group->id}}">{{__($group->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label"> {{ translate('Status')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option
                                            value="{{ \App\Models\Contact::ACTIVE }}">{{ translate('Subscribed')}}</option>
                                        <option
                                            value="{{ \App\Models\Contact::INACTIVE }}">{{ translate('UnSubscribed')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn bg--success">{{ translate('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="updateContact" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.phone.book.contact.update')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="card-title text-center text--light">{{ translate('Update Contact')}}</div>
                            </div>
                            <div class="card-body">

                                <x-phone-input id="update_contact_no" name="contact_no" class="w-100" countryCode="country_code"></x-phone-input>

                                <div class="mb-3">
                                    <label for="name" class="form-label">{{ translate('Name')}} <sup
                                            class="text--danger">*</sup></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           placeholder="{{ translate('Enter Name')}}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="group_id" class="form-label">{{ translate('Group')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="group_id" id="group_id" required>
                                        <option value="">{{ translate('Select Group')}}</option>
                                        @foreach($user->group as $group)
                                            <option value="{{$group->id}}">{{__($group->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">{{ translate('Status')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option
                                            value="{{ \App\Models\Contact::ACTIVE }}">{{ translate('Subscribed')}}</option>
                                        <option
                                            value="{{ \App\Models\Contact::INACTIVE }}">{{ translate('UnSubscribed')}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn bg--success">{{ translate('Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form action="{{ route('user.phone.book.group.toggle_active') }}" id="toggle-contacts" method="POST">@csrf <input
            id="contacts" type="hidden" name="contacts"></form>


    <div class="modal fade" id="deletegroup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.phone.book.contact.delete')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal_body2">
                        <div class="modal_icon2">
                            <i class="las la-trash-alt"></i>
                        </div>
                        <div class="modal_text2 mt-3">
                            <h6>{{ translate('Are you sure to want delete this contact?')}}</h6>
                        </div>
                    </div>
                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn bg--danger">{{ translate('Delete')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="modal fade" id="contactImport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('user.phone.book.contact.import')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="card-title text-center text--light">{{ translate('Update Contact')}}</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="group_id" class="form-label">{{ translate('Group')}} <sup
                                            class="text--danger">*</sup></label>
                                    <select class="form-select" name="group_id" id="group_id" required>
                                        <option value="">{{ translate('Select Group')}}</option>
                                        @foreach($user->group as $group)
                                            <option value="{{$group->id}}">{{__($group->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="file" class="form-label">{{ translate('File')}} <sup
                                            class="text--danger">*</sup></label>
                                    <input type="file" name="file" id="file" class="form-control" required="">
                                    <div class="form-text">{{ translate('Supported files: excel,')}}</div>
                                    <div class="form-text">{{ translate('Download file format from here')}} <a
                                            href="{{route('phone.book.demo.import.file')}}">{{ translate('xlsx')}}</a>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone_column"
                                           class="form-label">{{ translate('Phone Column Name (Optional)')}}</label>
                                    <div
                                        class="form-text">{{ translate('If the Excel sheet includes multiple columns with Phone Numbers, please indicate the specific column name that should be considered for importing.')}}</div>
                                    <input type="text" placeholder="Phone Column Name" name="phone_column"
                                           id="phone_column" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn bg--success">{{ translate('Submit')}}</button>
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
            $('.contact').on('click', function () {
                var modal = $('#updateContact');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('select[name=group_id]').val($(this).data('group_id'));
                modal.find('input[name=name]').val($(this).data('name'));
                modal.find('select[name=status]').val($(this).data('status'));
                modal.modal('show');
                setPhoneValue($(this).data('contact_no'), document.querySelector("#update_contact_no"));
            });

            $('.delete').on('click', function () {
                var modal = $('#deletegroup');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });

            $('#toggleStatus').on('click', function () {
                var newArray = [];
                $("input:checkbox[name=contact_id]:checked").each(function () {
                    newArray.push($(this).val());
                });

                console.log(newArray)

                $("#toggle-contacts").find("#contacts").val(newArray)

                $("#toggle-contacts").submit()
            });
        })(jQuery);
    </script>
@endpush
