<?php

namespace App\Http\Requests;

use App\Rules\NepaliPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('delivery_zone')) {
            $this->merge(['delivery_zone' => 'inside_valley']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new NepaliPhone],
            'email' => ['nullable', 'email', 'max:255'],
            'delivery_zone' => ['required', 'string', 'in:inside_valley,outside_valley'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_zone.required' => 'Please select your delivery zone (Inside Kathmandu Valley or Outside Valley).',
            'delivery_zone.in' => 'Invalid delivery zone selected.',
        ];
    }
}
