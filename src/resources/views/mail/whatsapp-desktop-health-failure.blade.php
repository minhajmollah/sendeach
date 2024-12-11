@component('mail::message')

# Hi {{ $user->name }},

Your Whatsapp Desktop PC APP is disconnected or failed to pass health Check.
To continue using this device please connect again by Reinitialising your SendEach Desktop APP.

### Device ID: {{ $device->device_id }}

@component('mail::button', ['url' => $url])
View Status
@endcomponent

In order to resend the pending messages sent through this gateway, please navigate to the WhatsApp logs and select all
the pending messages. Then, proceed to change the gateway for those messages.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
