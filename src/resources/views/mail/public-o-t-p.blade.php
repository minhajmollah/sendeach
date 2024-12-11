<x-mail::message>
{{ isset($username) ? 'Hello '.$username : 'Hi' }},

Thank you for choosing SendEach! To ensure the security of your account, here's your One-Time Password (OTP):

Your OTP: <b>{{$otp}}</b>
<br>
Please enter the code to complete your Registration or Login on <b>{{ $domain }}</b>.

Keep it confidential and do not share it with anyone.

For any inquiries or assistance, don't hesitate to contact our support team:

Tel: 18768172200 <br>
Email: david@sendeach.com

We appreciate your trust in SendEach and look forward to providing you with excellent messaging services!

Best regards,<br>
SendEach Team<br>
{{ route('home') }}
</x-mail::message>
