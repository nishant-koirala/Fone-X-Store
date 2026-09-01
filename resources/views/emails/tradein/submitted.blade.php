<x-mail::message>
# Trade-In Request Received

Hi {{ $valuation->customer->name }},

We have successfully received your trade-in request (#{{ $valuation->id }}). Our valuation team is currently reviewing your submission and will get back to you with an estimate soon!

### Device Details

**Brand:** {{ $valuation->device_brand }}<br>
**Model:** {{ $valuation->device_model }}<br>
**Condition summary:** {{ $valuation->condition_description }}

If you need to add any additional information, simply reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
