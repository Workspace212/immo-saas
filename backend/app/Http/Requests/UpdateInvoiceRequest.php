<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $invoice instanceof Invoice
            && ($this->user()?->can('update', $invoice) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = (new StoreInvoiceRequest())->rules();

        foreach ($rules as $field => $fieldRules) {
            $rules[$field] = array_merge(['sometimes'], array_values(array_diff($fieldRules, ['required'])));
        }

        $rules['agency_id'] = ['prohibited'];

        return $rules;
    }
}
