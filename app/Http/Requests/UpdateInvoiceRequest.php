<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id'     => ['sometimes', 'required', 'exists:clients,id'],
            'issue_date'    => ['sometimes', 'required', 'date'],
            'due_date'      => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'discount_type' => ['sometimes', 'string', Rule::in(['fixed', 'percent'])],
            'discount_value'=> ['sometimes', 'numeric', 'min:0'],
            'tax_rate'      => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'notes'         => ['nullable', 'string'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.id'    => ['sometimes', 'integer', 'exists:invoice_items,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.exists'       => 'Klien yang dipilih tidak ditemukan.',
            'due_date.after_or_equal'=> 'Jatuh tempo tidak boleh sebelum tanggal terbit.',
            'items.required'         => 'Minimal 1 baris item wajib diisi.',
            'items.min'              => 'Minimal 1 baris item wajib diisi.',
            'items.*.description.required' => 'Deskripsi item wajib diisi.',
            'items.*.quantity.required'    => 'Kuantitas wajib diisi.',
            'items.*.quantity.min'         => 'Kuantitas minimal 0.01.',
            'items.*.unit_price.required'  => 'Harga satuan wajib diisi.',
            'items.*.unit_price.min'       => 'Harga satuan minimal 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        $cleanItems = array_filter($items, fn ($item) => !empty(trim($item['description'] ?? '')));
        $this->merge([
            'items' => array_values($cleanItems),
        ]);
    }
}