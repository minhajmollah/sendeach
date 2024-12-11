@php use App\Models\WhatsappTemplate; @endphp
@extends(auth('web')->id() ? 'user.layouts.app' : 'admin.layouts.app')

@php
    $routePrefix = auth('web')->id() ? 'user' : 'admin';
@endphp

@php
    if(empty($whatsappTemplate)) {
        $whatsappTemplate = null;
        $isEdit = false;
        $route = route($routePrefix.'.business.whatsapp.template.store');
        $method = "POST";
    }else {
        $route = route($routePrefix.'.business.whatsapp.template.update', $whatsappTemplate->id);
        $method = "PUT";
        $isEdit = true;
    }

    $headerVariables = old('components.HEADER.example.header_texts') ?? optional($whatsappTemplate)->getTemplateParameters('HEADER') ?? [];
    $bodyVariables = old('components.BODY.example.body_text') ??  optional($whatsappTemplate)->getTemplateParameters('BODY') ?? [];
@endphp

@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <form action="{{ $route }}" method="POST">
                @csrf
                @if($method == "PUT")
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light">@lang('Request New Template')</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="whatsapp_business_id" class="form-label">@lang('Business Account') <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" id="whatsapp_business_id" name="whatsapp_business_id"
                                        @if($isEdit) disabled @endif
                                        required>
                                    @foreach($whatsappBusinessAccounts as $account)
                                        <option
                                            value="{{ $account->whatsapp_business_id }}"
                                            @if(old('whatsapp_business_id', optional($whatsappTemplate)->whatsapp_business_id) == $account->whatsapp_business_id)
                                                selected
                                            @endif>{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">@lang('Name') <sup
                                        class="text--danger">*</sup></label>
                                <input type="text" class="form-control" id="name" name="name"
                                       placeholder="@lang('Enter Name')"
                                       value="{{ old("name", optional($whatsappTemplate)->name) }}"
                                       @if($isEdit) disabled @endif required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">@lang('Category') <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" id="category" name="category" @if($isEdit) disabled
                                        @endif required>
                                    @foreach(WhatsappTemplate::$categories as $category)
                                        <option
                                            value="{{ $category }}" @if(old('category', optional($whatsappTemplate)->$category) == $category)
                                            "selected"
                                        @endif>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">@lang('Language') <sup
                                        class="text--danger">*</sup></label>
                                <select class="form-select" id="language" name="language" @if($isEdit) disabled
                                        @endif required>
                                    @foreach(WhatsappTemplate::$languages as $language)
                                        <option
                                            value="{{ $language }}" @if(old('language', optional($whatsappTemplate)->$language) == $language)
                                            "selected"
                                        @endif>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="fw-semibold fs-5 mt-4">Components</p>
                            <p class="text-secondary">
                                Type @{{VARIABLE_NO}} to add a variables to text. Ex: Hello @{{0}}. VARIABLE_NO should
                                start in 1.
                            </p>
                            <p class="text-danger">
                                Variables at start of text and end of text is not allowed. Your template will be
                                rejected if did not follow the guidelines.
                            </p>
                            <div class="my-2">
                                <label for="component_header_text" class="form-label">@lang('Header') </label>
                                <div class="mb-3">
                                        <textarea rows="2" class="form-control component_input" maxlength="255"
                                                  id="component_header_text" data-type="HEADER"
                                                  name="components[HEADER][text]"
                                                  placeholder="@lang('Enter Text')">{{ old('components.HEADER.text', optional($whatsappTemplate)->getComponentText('HEADER')) }}</textarea>
                                    <div class="variables">
                                        @foreach($headerVariables as $parameter)
                                            <input type="text" value="{{ $parameter }}" class="form-control my-2"
                                                   id="name" name="components[HEADER][example][header_text][]"
                                                   placeholder="Example Text" required>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="my-2">
                                <label for="component_body_text" class="form-label">@lang('Body') <sup
                                        class="text--danger">*</sup></label>
                                <div class="mb-3">
                                        <textarea rows="2" class="form-control component_input" maxlength="255"
                                                  id="component_body_text" data-type="BODY"
                                                  name="components[BODY][text]"
                                                  placeholder="@lang('Enter Text')"
                                                  required="">{{ old('components.BODY.text', optional($whatsappTemplate)->getComponentText('BODY')) }}</textarea>

                                    <div class="variables">
                                        @foreach($bodyVariables as $parameter)
                                            <input type="text" value="{{ $parameter }}" class="form-control my-2"
                                                   id="name" name="components[BODY][example][body_text][]"
                                                   placeholder="Example Text" required>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="my-2">
                                <label for="component_footer_text" class="form-label">@lang('Footer')</label>
                                <div class="mb-3">
                                        <textarea rows="2" class="form-control" maxlength="255"
                                                  id="component_footer_text" name="components[FOOTER][text]"
                                                  placeholder="@lang('Enter Text')">{{ old('components.FOOTER.text', optional($whatsappTemplate)->getComponentText('FOOTER')) }}</textarea>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <div class="modal_button2">
                                <a href="{{ route($routePrefix.'.business.whatsapp.template.index') }}"
                                   class="btn border-1 border-dark bg--secondary">@lang('Cancel')</a>
                                <button type="submit" class="bg--success">@lang('Submit')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection


@push('scriptpush')
    <script>
        (function ($) {
            "use strict";
            $('.component_input').on('change', function () {
                let inputElement = $(this);

                let vars = inputElement.val().match(/\{\{[0-9]}\}/g);

                if (!vars) {
                    return;
                }
                console.log(inputElement)

                let variableElem = inputElement.next('.variables');
                let name = inputElement.data('type');
                let html = '';

                vars.forEach((val, i) => {
                    html += `<input type="text" class="form-control my-2" id="name" name="components[${name}][example][${name.toLowerCase()}_text][]"
                                           placeholder="Example ${val}" required>`
                })

                variableElem.html(html)
            })
        })(jQuery);
    </script>
@endpush
