<?php

namespace App\Http\Requests\API\Order;

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
            "address_id" => "required|exists:addresses,id",
            "outlet_id" => "required|exists:outlets,id",
            "products" => "required|array",
            "products.*.product_id" => "required|integer|exists:products,id",
            "products.*.quantity" => "required|integer|min:1",
            "products.*.variants" => "nullable|array"
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
