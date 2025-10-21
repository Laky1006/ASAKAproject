@component('mail::message')
# Hi {{ $name }},

We noticed you haven’t booked any services recently — we miss you!  
Come back and check out our new offerings today.

@component('mail::button', ['url' => url('/')])
Browse Services
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
