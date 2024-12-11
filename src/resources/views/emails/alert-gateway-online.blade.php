<x-mail::message>
# Hi {{ $user->name }},

We are happy to inform you that the WhatsApp {{ $gateway }} gateway is now back online. You can now use WhatsApp to send and receive messages as usual.

We apologize for any inconvenience the outage may have caused. Thank you for your patience.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
