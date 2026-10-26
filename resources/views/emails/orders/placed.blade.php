<x-mail::message>
# Order Confirmation

Hi {{ $order->shipping_name ?? $order->customer->name }},

Thank you for your order! We have received your order (#{{ $order->id }}) and it is currently being processed.

### Order Summary

<x-mail::table>
| Item | Grade | Quantity | Price |
| :--- | :--- | :---: | :--- |
@foreach($order->items as $item)
| {{ $item->productCondition->product->name }} | {{ strtoupper($item->productCondition->grade) }} | {{ $item->quantity }} | Rs {{ number_format($item->price_at_purchase) }} |
@endforeach
| **Total** | | | **Rs {{ number_format($order->total) }}** |
</x-mail::table>

If you have any questions, feel free to reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
