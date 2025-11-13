<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seller_id' => ['sometimes','integer','exists:users,id'],
            'name' => ['sometimes','string','max:255'],
            'description' => ['sometimes','nullable','string'],
            'price' => ['sometimes','numeric','min:0'],
            'stock_quantity' => ['sometimes','integer','min:0'],
            'image' => ['sometimes','nullable','url'],
            'category' => ['sometimes','nullable','string','max:100'],
            'condition' => ['sometimes','nullable','in:new,used,refurbished'],
            'status' => ['sometimes','nullable','in:active,inactive,archived'],
        ];
    }
}