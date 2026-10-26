<?php

namespace App\Http\Controllers;

use App\Enums\TradeInStatusEnum;
use App\Http\Requests\StoreTradeInRequest;
use App\Mail\AdminTradeInAlert;
use App\Mail\TradeInSubmitted;
use App\Models\Customer;
use App\Models\TradeInValuation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    public function store(StoreTradeInRequest $request): RedirectResponse
    {
        // Honeypot spam check - redirect as if successful without processing or emailing
        if ($request->filled('secondary_address')) {
            return redirect()->route('home')->with('success', 'Trade-in request submitted successfully!');
        }

        $validated = $request->validated();

        // Compile structured condition details string
        $description = sprintf(
            'Overall Condition: %s%s',
            ucfirst($request->input('condition')),
            $request->filled('additional_notes') ? ' | Notes: '.trim($request->input('additional_notes')) : ''
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

        // Link user_id only if authenticated user is verified
        $verifiedUserId = (auth()->check() && auth()->user()?->hasVerifiedEmail()) ? auth()->id() : null;

        // Create pending TradeInValuation record
        $valuation = TradeInValuation::create([
            'customer_id' => $customer->id,
            'user_id' => $verifiedUserId,
            'device_brand' => trim($validated['device_brand']),
            'device_model' => trim($validated['device_model']),
            'condition_description' => $description,
            'estimated_value' => null,
            'status' => TradeInStatusEnum::PENDING,
        ]);

        session(['last_valuation_id' => $valuation->id]);

        try {
            if ($customer->email) {
                Mail::to($customer->email)->send(new TradeInSubmitted($valuation));
            }

            // Send alert to Admin
            $adminEmail = config('mail.admin_address', 'admin@fonex.com');
            Mail::to($adminEmail)->send(new AdminTradeInAlert($valuation));
        } catch (\Throwable $mailException) {
            Log::error("Failed sending trade-in notification emails for valuation #{$valuation->id}: {$mailException->getMessage()}", [
                'exception' => $mailException,
            ]);
        }

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
