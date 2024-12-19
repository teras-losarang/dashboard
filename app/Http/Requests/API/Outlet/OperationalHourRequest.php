<?php

namespace App\Http\Requests\API\Outlet;

use App\Facades\MessageFixer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OperationalHourRequest extends FormRequest
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
            "operational" => "required|array|min:7|max:7",
            "operational.*.day" => "required|string|max:10",
            "operational.*.open_time" => "required|string",
            "operational.*.close_time" => "required|string"
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
