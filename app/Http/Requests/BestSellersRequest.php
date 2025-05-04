<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Intervention\Validation\Rules\Isbn;

class BestSellersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'offset' => 0
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'author' => ['nullable', 'string'],
            'isbn' => ['nullable', 'array'],
            'isbn.*' => ['filled', 'string', 'regex:/^[0-9-]+$/', new Isbn()],
            'title' => ['nullable', 'string'],
            'offset' => [
                'sometimes',
                'integer',
                function (string $attribute, int $value, callable $fail) {
                    if ($value % 20 !== 0) {
                        $fail("The $attribute is invalid.");
                    }
                }
            ],
        ];
    }
}
