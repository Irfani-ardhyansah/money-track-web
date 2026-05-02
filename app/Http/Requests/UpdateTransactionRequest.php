<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'occurred_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'wallet_id' => ['required', 'exists:wallets,id'],
            'to_wallet_id' => [
                Rule::requiredIf(fn () => $this->type === 'transfer'),
                'nullable',
                'exists:wallets,id',
                'different:wallet_id',
            ],
            'category_id' => [
                Rule::requiredIf(fn () => in_array($this->type, ['income', 'expense'])),
                'nullable',
                'exists:categories,id',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
