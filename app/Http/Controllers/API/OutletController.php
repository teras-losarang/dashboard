<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Outlet\OperationalHourRequest;
use App\Http\Requests\API\Outlet\RegisterRequest;
use App\Http\Requests\API\Outlet\UpdateRequest;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OutletController extends Controller
{
    protected $outlet;

    public function __construct()
    {
        $this->outlet = new Outlet();
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        $user = $request->user();
        $outlet = $user->outlet;
        if ($outlet) {
            return MessageFixer::error("You are outlet, contact admin to activate.");
        }

        $images = [];
        foreach ($request->file('images') as $image) {
            $images[] = $image->store('outlets');
        }

        try {
            $outlet = $this->outlet->create([
                "name" => $request->name,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "address" => $request->address,
                "description" => $request->description,
                "images" => json_encode($images),
                "user_id" => $user->id,
                "operational_hour" => json_encode([["day" => "Senin", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Selasa", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Rabu", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Kamis", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Jumat", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Sabtu", "open_time" => "09:00:00", "close_time" => "17:00:00"], ["day" => "Minggu", "open_time" => "09:00:00", "close_time" => "17:00:00"]])
            ]);

            DB::commit();
            return MessageFixer::success("Outlet has been registered");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $outlet = $user->outlet;
        if (!$outlet || $outlet->status != 1) {
            return MessageFixer::error("You are not outlet.");
        }

        $outlet->operational_hour = json_decode($outlet->operational_hour, true);

        $images = json_decode($outlet->images, true);
        $images = array_map(function ($image) {
            return asset(Storage::url($image));
        }, $images);
        $outlet->images = $images;

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $outlet);
    }

    public function updateOperationalHour(OperationalHourRequest $request)
    {
        DB::beginTransaction();

        $user = $request->user();
        $outlet = $user->outlet;
        if (!$outlet || $outlet->status != 1) {
            return MessageFixer::error("You are not outlet.");
        }

        try {
            $outlet->operational_hour = json_encode($request->operational);
            $outlet->save();

            DB::commit();
            return MessageFixer::success("Outlet has been updated");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function update(UpdateRequest $request)
    {
        DB::beginTransaction();

        $user = $request->user();
        $outlet = $user->outlet;
        if (!$outlet || $outlet->status != 1) {
            return MessageFixer::error("You are not outlet.");
        }

        $images = [];
        if ($request->hasFile("images")) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('outlets');
            }
        } else {
            $images = json_decode($outlet->images, true);
        }

        try {
            $outlet->update([
                "name" => $request->name,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "address" => $request->address,
                "description" => $request->description,
                "images" => json_encode($images),
            ]);

            DB::commit();
            return MessageFixer::success("Outlet has been updated");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
