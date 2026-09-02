<x-mail::message>
# New Trade-In Request Action Required

A new trade-in valuation request has been submitted on Fone-X-Store.

## Customer Information
- **Name:** {{ $valuation->customer->name }}
- **Phone:** {{ $valuation->customer->phone }}
- **Email:** {{ $valuation->customer->email ?? 'N/A' }}

## Device Details
- **Brand:** {{ $valuation->device_brand }}
- **Model:** {{ $valuation->device_model }}
- **Condition Description:** 
{{ $valuation->condition_description }}

<x-mail::button :url="route('filament.admin.resources.trade-in-valuations.edit', $valuation)">
Review Request
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
