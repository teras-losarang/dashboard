<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            return MessageFixer::render(message: "Success", data: [
                "app_name" => env("APP_NAME"),
                "secret_key" => env("SECRET_API"),
                "maps_key" => env("MAPS_KEY"),
            ]);
        } catch (\Throwable $th) {
            return MessageFixer::error($th->getMessage());
        }
    }
}
