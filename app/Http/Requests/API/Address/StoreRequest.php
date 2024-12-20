<?php

namespace App\Http\Requests\API\Address;

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
            "province_id" => "required|integer|exists:provinces,id",
            "regency_id" => "required|integer|exists:regencies,id",
            "district_id" => "required|integer|exists:districts,id",
            "village_id" => "required|integer|exists:villages,id",
            "name" => "required|max:100",
            "phone" => "required|max:15|min:8",
            "detail" => "max:200",
            "is_default" => "required|boolean"
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
