@component('mail::message')
# Hi {{ $user->name }},

Your Whatsapp Device is disconnected or failed to pass health Check.
To continue using this device please connect again by going to whatsapp Settings in your SendEach.com.

### Device Name: {{ $device->name }}
### Device Number: {{ $device->number }}

@component('mail::button', ['url' => $url])
Connect Whatsapp Device
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
