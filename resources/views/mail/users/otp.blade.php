<x-mail::message>
# Hi {{$otp->user->name}}

Your OTP is {{$otp->code}} and valid for only 5 mintues.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
