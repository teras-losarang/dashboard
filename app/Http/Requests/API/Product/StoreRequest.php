<?php

namespace App\Http\Requests\API\Product;

use App\Facades\MessageFixer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
            "categories" => "required|array",
            "categories.*" => "numeric|exists:categories,id",
            "name" => "required|max:150",
            "price" => "required|numeric|min:0",
            "description" => "required|max:300",
            "enable_variant" => "required|boolean",
            "images" => "required|array|max:5",
            "images.*" => "image|mimes:png,jpg,jpeg|max:2048",
            "variants" => [$this->input("enable_variant") ? "required" : "nullable", "array"],
            "variants.*.name" => [$this->input("enable_variant") ? "required" : "nullable", "max:100"],
            "variants.*.price" => [$this->input("enable_variant") ? "required" : "nullable", "numeric", "min:0"],
            "variants.*.status" => [$this->input("enable_variant") ? "required" : "nullable", "boolean"],
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
