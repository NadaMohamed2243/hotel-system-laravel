<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        // check if update request
        $isUpdateRequest = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name' => ['required', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user),
            ],
            'role' => ['required', 'in:admin,manager,receptionist'],
            'national_id' => [
                'nullable',
                'max:255',
                Rule::unique('users')->ignore($this->user),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg'],
        ];

        // only require password for new users
        if (!$isUpdateRequest) {
            $rules['password'] = ['required', 'min:6'];
        }

        return $rules;
    }
}
