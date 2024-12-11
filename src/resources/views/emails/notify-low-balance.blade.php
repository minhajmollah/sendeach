@component('mail::message')
# Hi {{$user->name}},

## You are low on ( ${{ $user->getAvailableDepositInDollars() }} ) balance, [ {{$user->credit}} Credits Left].
#### Please buy credits to avoid interruptions and send messages without watermarks.
#### You will be switched to web gateway from sendEach business Gateway when credit goes to 0.

@component('mail::button', ['url' => route('user.credits.create')])
Buy Credits
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
