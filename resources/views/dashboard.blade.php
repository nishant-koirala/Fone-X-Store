<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-2xl text-brand-charcoal leading-tight">
            {{ __('My Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Order History Section -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-brand-offwhite flex justify-between items-center">
                    <h3 class="font-display text-lg uppercase tracking-tight text-brand-charcoal">Order History</h3>
                </div>
                <div class="p-6">
                    @if($orders->isEmpty())
                        <p class="text-gray-500 font-sans text-sm">You haven't placed any orders yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-mono font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-mono font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-mono font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-mono font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-mono font-bold text-gray-500 uppercase tracking-wider">Items</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-brand-charcoal">#{{ $order->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">${{ number_format($order->total, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $order->status == 'completed' ? 'green' : ($order->status == 'pending' ? 'yellow' : 'gray') }}-100 text-{{ $order->status == 'completed' ? 'green' : ($order->status == 'pending' ? 'yellow' : 'gray') }}-800 uppercase">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <ul class="list-disc pl-4">
                                                    @foreach($order->items as $item)
                                                        <li>{{ $item->productCondition->product->name ?? 'Unknown Product' }} (x{{ $item->quantity }})</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Trade-In Valuations Section -->
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-brand-offwhite flex justify-between items-center">
                    <h3 class="font-display text-lg uppercase tracking-tight text-brand-charcoal">Trade-In Requests</h3>
                    <a href="{{ route('trade-in.create') }}" class="text-xs font-mono uppercase tracking-wider font-bold text-brand-red hover:text-brand-red-dark">Submit New Device</a>
                </div>
                <div class="p-6">
                    @if($tradeIns->isEmpty())
                        <p class="text-gray-500 font-sans text-sm">You haven't submitted any trade-in requests.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($tradeIns as $trade)
                                <div class="border border-gray-200 p-4 rounded-md shadow-sm relative">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-brand-charcoal text-lg">{{ $trade->device_brand }} {{ $trade->device_model }}</h4>
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-{{ $trade->status == 'approved' ? 'green' : ($trade->status == 'pending' ? 'yellow' : 'red') }}-100 text-{{ $trade->status == 'approved' ? 'green' : ($trade->status == 'pending' ? 'yellow' : 'red') }}-800">
                                            {{ $trade->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-4">{{ $trade->created_at->format('M d, Y') }}</p>
                                    
                                    <div class="bg-gray-50 p-3 rounded text-sm mb-4">
                                        <p class="text-gray-700 truncate" title="{{ $trade->condition_description }}">{{ $trade->condition_description }}</p>
                                    </div>
                                    
                                    <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                                        <span class="text-xs font-mono text-gray-500 uppercase">Est. Value</span>
                                        <span class="font-bold text-brand-charcoal {{ $trade->estimated_value ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $trade->estimated_value ? '$' . number_format($trade->estimated_value, 2) : 'Pending Evaluation' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
