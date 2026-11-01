<?php

namespace App\Http\Requests\Pharmacy;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('prescription.create') || $this->user()?->isSuperAdmin() || $this->user()?->isDoctor();
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_item_id' => ['required', 'exists:stock_items,id'],
            'items.*.drug_name' => ['required', 'string', 'max:200'],
            'items.*.dose' => ['required', 'string', 'max:50'],
            'items.*.route' => ['nullable', 'string', 'max:50'],
            'items.*.frequency' => ['required', 'string', 'max:50'],
            'items.*.duration' => ['nullable', 'string', 'max:50'],
            'items.*.quantity_prescribed' => ['required', 'integer', 'min:1'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
