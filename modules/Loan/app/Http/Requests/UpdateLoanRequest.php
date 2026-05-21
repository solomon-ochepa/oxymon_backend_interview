<?php

namespace Modules\Loan\App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanRequest extends FormRequest
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
     * All fields are optional on update; only the supplied ones are validated.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:1', 'max:9999999999'],
            'interest' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'term' => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'approved', 'active', 'paid', 'rejected'])],
        ];
    }
}
