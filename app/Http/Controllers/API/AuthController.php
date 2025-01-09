<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $user, $role;

    public function __construct()
    {
        $this->user = new User();
        $this->role = new Role();
    }
    
    public function login(LoginRequest $request)
    {
        DB::beginTransaction();

        $user = $this->user->whereEmail($request->email)->first();
        if (!$user) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: 'Account not found!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: 'Account not found!');
        }

        try {
            $roles = $user->roles->pluck("name")->toArray();
            $token = $user->createToken('api', $roles)->plainTextToken;
            $user->secret = $token;
            $user->api_key = env("SECRET_API", "Not Set");
            $user->map_key = env("MAPS_KEY", "Not Set");

            unset($user->roles);

            DB::commit();
            return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Login Successfully!", data: $user);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
    
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        $userCheck = $this->user->whereEmail($request->email)->count();
        if ($userCheck > 0) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: 'Account already exists!');
        }

        $userCheck = $this->user->wherePhone($request->phone)->count();
        if ($userCheck > 0) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: 'Account already exists!');
        }

        try {
            $user = $this->user->create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $user->assignRole(Role::where('id', $this->user::ROLE_CUSTOMER)->first());

            DB::commit();
            return MessageFixer::success(message: "Register Successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    /**
     * @OA\Post(
     *      path="/api/auth/me",
     *      operationId="Me",
     *      tags={"Auth"},
     *      summary="Me",
     *      description="Me",
     *      security={ {"sanctum": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="SUCCESS/ERROR by code in json result",
     *       ),
     *     )
     */
    public function me()
    {
        $user = request()->user();
        if ($user->outlet && $user->outlet->status == 1) {
            $user->is_outlet = 1;
        } else {
            $user->is_outlet = 0;
        }

        unset($user->outlet);

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $user);
    }

    public function signature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "path" => "required"
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: "Fill data correctly!", data: $validator->errors());
        }

        $method = "POST";
        $secretKey = env("SECRET_API");
        $timestamp = time();

        $params = http_build_query([
            "method" => $method,
            "path" => $request->path,
            "timestamp" => $timestamp
        ]);
        $params = base64_encode($params);

        $signature = hash_hmac("sha256", $params, $secretKey);

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: [
            "timestamp" => $timestamp,
            "signature" => $signature,
        ]);
    }
}
