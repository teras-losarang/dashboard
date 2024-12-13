<?php

namespace App\Facades;

use Illuminate\Support\Str;

class MessageFixer
{
    const DATA_ERROR = "111";
    const DATA_OK = "000";

    public static function render($code = self::DATA_OK, $message = null, $data = null, $paginate = null)
    {
        $result = [
            "status" => $code == self::DATA_OK ? true : false,
            "code" => $code,
        ];

        if ($message) {
            $result["messages"] = $message;
        }

        if ($data) {
            $result["data"] = $data;
        }

        if ($paginate) {
            $result["pagination"] = $paginate;
        }

        return response()->json($result);
    }

    public static function success($message)
    {
        return self::render(code: self::DATA_OK, message: $message);
    }

    public static function error($message)
    {
        return self::render(code: self::DATA_ERROR, message: $message);
    }
}
