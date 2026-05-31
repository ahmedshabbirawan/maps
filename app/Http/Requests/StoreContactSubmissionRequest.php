<?php

namespace App\Http\Requests;

use App\Services\SimpleCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email', 'max:255', 'required_without:mobile'],
            'mobile' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s\-\+\(\)]+$/', 'required_without:email'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'captcha' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Please provide an email or mobile number.',
            'mobile.required_without' => 'Please provide an email or mobile number.',
            'mobile.regex' => 'The mobile number format is invalid.',
            'message.min' => 'Your message must be at least 10 characters.',
            'captcha.required' => 'Please solve the security check.',
            'captcha.integer' => 'The security check answer must be a number.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $captcha = app(SimpleCaptcha::class);

            if (! $captcha->validate($this->input('captcha'))) {
                $validator->errors()->add('captcha', 'The security check answer is incorrect.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? trim($this->input('email')) : null,
            'mobile' => $this->filled('mobile') ? trim($this->input('mobile')) : null,
        ]);
    }
}
