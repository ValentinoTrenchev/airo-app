<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'age'         => ['required', 'string'],
            'currency_id' => ['required', 'in:EUR,GBP,USD'],
            'start_date'  => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date'    => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $raw = $this->input('age', '');
            $parts = explode(',', $raw);

            foreach ($parts as $part) {
                $value = trim($part);

                if (!is_numeric($value) || (int) $value != $value) {
                    $validator->errors()->add('age', 'Each age must be a whole number.');
                    return;
                }

                $age = (int) $value;

                if ($age < 18 || $age > 70) {
                    $validator->errors()->add('age', "Age {$age} is outside the allowed range of 18-70.");
                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'age.required'              => 'Please provide at least one age.',
            'age.string'                => 'Ages must be provided as a comma-separated string.',
            'currency_id.required'      => 'Please select a currency.',
            'currency_id.in'            => 'Currency must be one of: EUR, GBP, USD.',
            'start_date.required'       => 'Please provide a start date.',
            'start_date.date'           => 'Start date must be a valid date.',
            'start_date.date_format'    => 'Start date must be in YYYY-MM-DD format.',
            'start_date.before_or_equal'=> 'Start date must be on or before the end date.',
            'end_date.required'         => 'Please provide an end date.',
            'end_date.date'             => 'End date must be a valid date.',
            'end_date.date_format'      => 'End date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal'   => 'End date must be on or after the start date.',
        ];
    }
}
