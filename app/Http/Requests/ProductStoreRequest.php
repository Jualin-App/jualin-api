<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_id' => ['required','integer','exists:users,id'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0'],
            'stock_quantity' => ['required','integer','min:0'],
            'image' => ['nullable','url'],
            'category' => ['nullable','string','max:100'],
            'condition' => ['nullable','in:new,used,refurbished'],
            'status' => ['nullable','in:active,inactive,archived'],
        ];
    }
}