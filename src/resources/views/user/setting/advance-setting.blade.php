@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Advanced Setting</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.advanced.save') }}" method="post">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="" class="mb-2">Domain Name</label>
                                            <select id="domain-input" name="doamin_name[]" multiple="multiple"
                                                style="width: 100%;">
                                                @if (count($restrictions) > 0)
                                                    @foreach ($restrictions as $restriction)
                                                        <option value="{{ $restriction->domain_name }}" selected>
                                                            {{ $restriction->domain_name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>

                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="d-flex">
                                            <div class="form-check form-switch form-switch-lg">
                                                <input class="form-check-input" name="status" type="checkbox"
                                                    value="1"
                                                    @if (count($restrictions) > 0) @if ($restrictions[0]?->status == 1)
                                                        checked @endif
                                                    @endif

                                                id="is_enabled_1" <label class="form-check-label" for="is_enabled">Only
                                                    Allow These </label>
                                            </div>
                                            <div class="form-check form-switch form-switch-lg ms-3">
                                                <input class="form-check-input" name="status" type="checkbox"
                                                    value="2" id="is_enabled_2"
                                                    @if (count($restrictions) > 0) @if ($restrictions[0]?->status == 2)
                                                        checked @endif
                                                    @endif

                                                <label class="form-check-label" for="is_enabled">Only Block These
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <button class="btn btn-success" type="submit">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-5 pb-4 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Instructions for Advanced Web Bot Settings
                </div>
                <div class="card-body">
                    <h5 class="card-title"><strong>URL Entry:</strong></h5>
                    <ul>
                        <li>Avoid entering "http://" or "https://" before the URL.</li>
                    </ul>

                    <h5 class="card-title"><strong>Wildcard Usage:</strong></h5>
                    <ul>
                        <li>Use the asterisk (*) as a wildcard in the URL. It can block or show any URL containing the
                            portion
                            before the asterisk.</li>
                    </ul>

                    <h5 class="card-title"><strong>Saving Changes:</strong></h5>
                    <ul>
                        <li>Click the "Submit" button to save your configured settings before enabling the "Show" or "Block"
                            options.</li>
                    </ul>

                    <h5 class="card-title"><strong>Support Contact:</strong></h5>
                    <ul>
                        <li>If you encounter any issues within this section, reach out to our support team via a support
                            ticket
                            for assistance.</li>
                    </ul>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#domain-input').select2({
                    tags: true,
                    tokenSeparators: [',', ' '], // Allow creating a new tag when comma or space is pressed
                    placeholder: "Enter domain names"
                });

                $(document).ready(function() {
                    $('#is_enabled_1').change(function() {
                        if ($(this).prop('checked')) {
                            $('#is_enabled_2').prop('checked', false);
                        }
                    });

                    $('#is_enabled_2').change(function() {
                        if ($(this).prop('checked')) {
                            $('#is_enabled_1').prop('checked', false);
                        }
                    });
                    $('#is_enabled_1, #is_enabled_2').change(function() {
                        // Check if the select has any selected value
                        if ($('#domain-input').val().length === 0) {
                            toastr["error"]("Please select a domain name first.");
                            $(this).prop('checked', false); // Uncheck the checkbox
                            return; // Exit the function to prevent the AJAX request
                        }
                        @if (!$hasRestrictions)
                            toastr["error"]("Please save a domain name first.");
                            // Uncheck the checkbox
                            return; // Exit the function to prevent the AJAX request
                        @endif

                        var checkboxId = $(this).attr('id');
                        var isChecked = $(this).prop('checked');
                        var status = 0;

                        if (checkboxId === 'is_enabled_1') {
                            status = isChecked ? 1 : 0;
                        } else if (checkboxId === 'is_enabled_2') {
                            status = isChecked ? 2 : 0;
                        }

                        $.ajax({
                            type: 'POST',
                            url: "{{ route('user.status') }}",
                            data: {
                                checkbox: checkboxId,
                                status: status
                            },
                            success: function(response) {


                                toastr["success"]("Status updated successfully")
                                toastr.options = {
                                    "closeButton": true,
                                    "debug": false,
                                    "newestOnTop": false,
                                    "progressBar": true,
                                    "positionClass": "toast-bottom-right",
                                    "preventDuplicates": false,
                                    "onclick": null,
                                    "showDuration": "100",
                                    "hideDuration": "1000",
                                    "timeOut": "5000",
                                    "extendedTimeOut": "1000",
                                    "showEasing": "swing",
                                    "hideEasing": "linear",
                                    "showMethod": "fadeIn",
                                    "hideMethod": "fadeOut"
                                }
                            },
                            error: function(error) {
                                // Handle error response
                            }
                        });
                    });
                });

            });
        </script>
    </section>
@endsection
