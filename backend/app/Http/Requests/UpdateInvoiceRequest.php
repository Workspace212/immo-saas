<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

        $rules['agency_id'] = ['sometimes', 'integer', 'exists:agencies,id'];

        return $rules;
    }
}
