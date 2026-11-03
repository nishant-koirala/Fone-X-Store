<?php

namespace App\Http\Requests;

use App\Rules\NepaliPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTradeInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_brand' => ['required', 'string', 'max:100'],
            'device_model' => ['required', 'string', 'max:255'],
            'condition' => ['required', 'string', 'in:flawless,good,broken'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', new NepaliPhone],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'additional_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
