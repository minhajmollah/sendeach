@extends('user.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="card b-radius-5 overflow-hidden profile-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-stripped">
                            <thead class="bg--lite--violet">
                            <th scope="col" class="text-white">Sr #</th>
                            <th scope="col" class="text-white">Name</th>
                            <th scope="col" class="text-white">Abilities</th>
                            <th scope="col" class="text-white">Last Used At</th>
                            <th scope="col" class="text-white">Generated At</th>
                            <th scope="col" class="text-white">Actions</th>
                            </thead>
                            <tbody>
                            @forelse ($tokens as $token)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $token->name }}</td>
                                    <td><div class="text-wrap">
                                            {{ join(', ', array_map(fn($ability) => \App\Models\User::ABILITIES[$ability], $token->abilities)) }}
                                        </div></td>
                                    <td>{{ $token->last_used_at?->format('M d, Y h:i A') ?? 'NA' }}</td>
                                    <td>{{ $token->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="py-2">
                                        <button class="btn btn--danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteModel_{{ $token->id }}">Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No API Token generated!</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <a href="javascript:void(0);" class="support-ticket-float-btn" data-bs-toggle="modal"
           data-bs-target="#createToken"
           title="{{ translate('Create New Token') }}">
            <i class="fa fa-plus ticket-float"></i>
        </a>

        <div class="modal fade" id="createToken" tabindex="-1" aria-labelledby="createAPITokenModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('user.sanctum-token.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-header bg--lite--violet">
                                    <div
                                        class="card-title text-center text--light">{{ translate('Create New API Token') }}
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">{{ translate('Name') }} <sup
                                                class="text--danger">*</sup></label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               placeholder="{{ translate('Enter Name') }}" required>
                                    </div>

                                    <div class="my-4 py-2">
                                        <h6 class="fw-bold">Token Abilities</h6>
                                        <div class="d-flex flex-column">
                                            @foreach($abilities as $ability => $name)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="abilities[]" value="{{ $ability }}" id="{{ $ability }}">
                                                    <label class="form-check-label" for="{{ $ability }}">
                                                        {{ $name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="alert alert-info">
                                        Kindly refrain from making your access token public. It's important to restrict
                                        the token's capabilities and employ distinct tokens for various tasks.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal_button2">
                            <button type="button" class=""
                                    data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                            <button type="submit" class="bg--success">{{ translate('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @foreach ($tokens as $token)
        <div class="modal fade" id="deleteModel_{{ $token->id }}" tabindex="-1" aria-labelledby="deleteAPITokenModal"
             aria-hidden="true">
            <div class="modal-dialog">
                <form class="modal-content" action="{{ route("user.sanctum-token.destroy", $token) }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        @method("DELETE")
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="card-title text-center text--light">{{ translate('Confirm Delete Token?') }}
                                </div>
                            </div>
                            <div class="card-body">
                                <p>
                                    <strong>
                                        Are you sure you wish to delete this token: [{{ $token->name }}]?
                                    </strong>
                                    <br>
                                    <br>
                                    <em>This action is non-reversable!</em>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger"
                                data-bs-dismiss="modal">{{ translate('Delete') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @if (session()->has('accessToken'))
        <div class="modal fade" id="tokenModel" tabindex="-1" aria-labelledby="showAPITokenModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header bg--lite--violet">
                                <div class="card-title text-center text--light">{{ translate('API Token Generated') }}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="timelocation" class="form-label">New API Token</label>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control"
                                               value="{{ session()->get('accessToken') }}" id="accessToken"
                                               aria-describedby="basic-addon2" readonly="">
                                        <div class="input-group-append pointer">
                                            <span class="input-group-text bg--success text--light" id="basic-addon2"
                                                  onclick="copyAccessToken()">Copy</span>
                                        </div>
                                    </div>
                                </div>
                                <p>
                                    <em>Please store the token in a safe place as this token will be only shown once.
                                        <br>You will have to generate a new token if you loose this one.</em>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="modal_button2">
                        <button type="button" class="" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scriptpush')
    <script>
        "use strict";

        jQuery(document).ready(function () {
            if (jQuery('#tokenModel')) {
                jQuery('#tokenModel').modal('show');
            }
        });

        function copyAccessToken() {
            var copyText = document.getElementById("accessToken");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', "API Token copied!");
        }
    </script>
@endpush
