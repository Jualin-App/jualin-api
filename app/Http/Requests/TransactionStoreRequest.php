<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = Auth::user();

        return [
            'seller_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn([$user ? $user->id : null]),
                function ($attribute, $value, $fail) {
                    $seller = \App\Models\User::find($value);
                    if ($seller && $seller->role !== 'seller') {
                        $fail('The selected seller must have a seller role.');
                    }
                },
            ],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'seller_id.required' => 'Seller ID is required',
            'seller_id.exists' => 'Selected seller does not exist',
            'seller_id.not_in' => 'You cannot create a transaction with yourself as the seller',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.product_id.required' => 'Product ID is required for each item',
            'items.*.product_id.exists' => 'One or more selected products do not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
