<?php

namespace App\Http\Requests\API\Outlet;

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
            "name" => "required|max:150",
            "latitude" => "required|max:15",
            "longitude" => "required|max:15",
            "address" => "required|max:200",
            "description" => "required|max:255",
            "images" => "required|array|max:3",
            "images.*" => "image|mimes:png,jpg,jpeg|max:2048",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
            "status" => false,
            "code" => MessageFixer::DATA_ERROR,
            "messages" => "Fill data correctly!",
            "data" => $validator->errors(),
        ], JsonResponse::HTTP_OK);

        throw new ValidationException($validator, $response);
    }
}
