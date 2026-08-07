<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TradeInValuation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TradeInController extends Controller
{
    /**
     * Display the trade-in device valuation request form.
     */
    public function create(): View
    {
        return view('trade-in.create');
    }

    /**
     * Store a new trade-in valuation request in pending status.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'device_brand' => 'required|string|max:100',
            'device_model' => 'required|string|max:255',
            'screen_condition' => 'required|string|max:255',
            'battery_health' => 'required|string|max:255',
            'physical_condition' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Compile structured condition details string
        $description = sprintf(
            "Screen: %s | Battery: %s | Body: %s%s",
            $request->input('screen_condition'),
            $request->input('battery_health'),
            $request->input('physical_condition'),
            $request->filled('notes') ? " | Notes: " . trim($request->input('notes')) : ""
        );

        // Find or create customer record by phone number
        $customer = Customer::firstOrCreate(
            ['phone' => trim($request->input('customer_phone'))],
            [
                'name' => trim($request->input('customer_name')),
                'address' => 'Trade-In Request Submission',
            ]
        );

        $customer->update([
            'name' => trim($request->input('customer_name')),
        ]);

        // Create pending TradeInValuation record
        $valuation = TradeInValuation::create([
            'customer_id' => $customer->id,
            'device_brand' => trim($request->input('device_brand')),
            'device_model' => trim($request->input('device_model')),
            'condition_description' => $description,
            'estimated_value' => null,
            'status' => 'pending',
        ]);

        session(['last_valuation_id' => $valuation->id]);

        return redirect()->route('trade-in.confirmation', $valuation)
            ->with('success', 'Trade-in request submitted successfully!');
    }

    /**
     * Display trade-in valuation confirmation screen.
     */
    public function confirmation(TradeInValuation $valuation): View|RedirectResponse
    {
        // Verification check to ensure order belongs to current session
        if (session('last_valuation_id') != $valuation->id) {
            return redirect()->route('home')->with('error', 'Unauthorized access to trade-in valuation page.');
        }

        $valuation->load('customer');

        return view('trade-in.confirmation', compact('valuation'));
    }
}
