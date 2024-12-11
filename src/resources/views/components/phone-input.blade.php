@props(['label' => 'Phone','id' => 'phone', 'name' => 'phone', 'countryCode' => 'country_code', 'placeholder' => '9876543210',
'value' => value(fn($val) => $val ? '+'.$val : null , old('phone_code').old('phone'))])

<label for="{{ $id }}" class="form-label">{{ $label }}<sup class="text--danger">*</sup></label>
<input id="{{ $id }}" pattern="[0-9]{10}" placeholder="{{ $placeholder }}"
       {{ $attributes->merge(['class' => 'form-control']) }} name="{{ $name }}" value="{{ $value }}"
       type="tel" required>
<input type="hidden" name="{{ $countryCode }}" id="{{ $countryCode }}">

<script>
    $(document).ready(function () {
        $('#{{ $id }}').siblings('#{{ $countryCode }}').val(initPhone(document.querySelector("#{{ $id }}")).getSelectedCountryData().dialCode);
    })
</script>

@pushonce('scriptpush')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>

    <script>

        function initPhone(input) {
            let iti = intlTelInput(input, {
                initialCountry: "auto",
                geoIpLookup: callback => {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("us"));
                },
                separateDialCode: true,
                utilsScript: "/intl-tel-input/js/utils.js?1690975972744", // just for formatting/placeholders etc
            });

            input.addEventListener("countrychange", function (e) {
                $(input).parent().siblings('#{{ $countryCode }}').val(iti.getSelectedCountryData().dialCode);
            });

            $(input).data('iti', iti);
            $(input).parent().siblings('#{{ $countryCode }}').val(iti.getSelectedCountryData().dialCode);

            return iti;
        }


        function setPhoneValue(phone, input) {
            input.value = '+'+phone
            let iti = $(input).data('iti');
            iti.destroy()
            iti = initPhone(input)
        }
    </script>
@endpushonce


@pushonce('stylepush')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.css">

    <style>
        .iti {
            width: 100%;
        }
    </style>
@endpushonce
