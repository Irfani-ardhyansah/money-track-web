<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id'   => ['required', 'exists:wallets,id'],
            'direction'   => ['required', 'in:add,subtract'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'occurred_at' => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'wallet_id.required' => 'Dompet wajib dipilih.',
            'wallet_id.exists'   => 'Dompet tidak ditemukan.',
            'direction.required' => 'Arah penyesuaian wajib dipilih.',
            'amount.required'    => 'Jumlah wajib diisi.',
            'amount.min'         => 'Jumlah minimal Rp 1.',
            'occurred_at.required' => 'Tanggal wajib diisi.',
        ];
    }
}
