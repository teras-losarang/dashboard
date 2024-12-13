<?php

namespace App\Http\Requests\API\Auth;

use App\Facades\MessageFixer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'phone' => 'required|min:9|max:15',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:255|same:confirm_password',
            'confirm_password' => 'required|min:6|max:255|same:password'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
            "data" => $validator->errors(),
            "messages" => "Fill data correctly!",
            "status" => false,
            "code" => MessageFixer::DATA_ERROR
        ], JsonResponse::HTTP_OK);

        throw new ValidationException($validator, $response);
    }
}
