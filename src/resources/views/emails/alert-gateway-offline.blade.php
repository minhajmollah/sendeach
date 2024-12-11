<x-mail::message>
# Hi {{ $user->name }},


Our WhatsApp {{ $gateway }} gateway is currently down for maintenance. Please switch to our Business Gateway or another online gateway to avoid any disruptions in your communications.


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
