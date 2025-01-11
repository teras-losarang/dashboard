<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Filters\Outlet\IsFavorite;
use App\Filters\Outlet\OutletId;
use App\Filters\Outlet\Search;
use App\Filters\Outlet\Slug;
use App\Filters\Outlet\SortBy;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Outlet\OperationalHourRequest;
use App\Http\Requests\API\Outlet\RegisterRequest;
use App\Http\Requests\API\Outlet\UpdateRequest;
use App\Models\Outlet;
use App\Models\OutletFavorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OutletController extends Controller
{
    protected $outlet, $favorite;

    public function __construct()
    {
        $this->outlet = new Outlet();
        $this->favorite = new OutletFavorite();
    }

    public function index(Request $request)
    {
        $outlets = app(Pipeline::class)
            ->send($this->outlet->query())
            ->through([
                Search::class,
                OutletId::class,
                Slug::class,
                SortBy::class,
                IsFavorite::class
            ])
            ->thenReturn()
            ->paginate($request->per_page);

        $outlets->getCollection()->transform(function ($outlet) {
            $images = json_decode($outlet->images, true);
            $imageConverts = [];
            foreach ($images as $image) {
                $imageConverts[] = asset(Storage::url($image));
            }

            $outlet->images = $imageConverts;
            $outlet->operational_hour = json_decode($outlet->operational_hour, true);

            return $outlet;
        });

        if (($request->has("outlet_id") && $request->outlet_id > 0) || ($request->has("slug") && $request->slug)) {
            if (count($outlets->items()) < 1) {
                return MessageFixer::error("Data outlet not found.");
            }

            return MessageFixer::render(MessageFixer::DATA_OK, "Success", $outlets[0]);
        }

        if (count($outlets->items()) < 1) {
            return MessageFixer::error("Data outlet is empty.");
        }

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $outlets->items(), paginate: ($outlets instanceof LengthAwarePaginator) && count($outlets->items()) > 0  ? [
            "current_page" => $outlets->currentPage(),
            "last_page" => $outlets->lastPage(),
            "total" => $outlets->total(),
            "from" => $outlets->firstItem(),
            "to" => $outlets->lastItem(),
        ] : null);
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

    public function listFavorite(Request $request)
    {
        $request->merge([
            "is_favorite" => 1
        ]);

        return $this->index($request);
    }

    public function storeFavorite(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(message: "Fill data correctly!", code: MessageFixer::DATA_ERROR, data: $validator->errors());
        }

        $user = $request->user();

        try {
            DB::commit();

            $favorite = $this->favorite->where([
                "outlet_id" => $request->outlet_id,
                "user_id" => $user->id
            ])->first();

            if ($favorite) {
                $favorite->delete();
                return MessageFixer::success("Outlet has been removed from favorite");
            }

            $this->favorite->create([
                "outlet_id" => $request->outlet_id,
                "user_id" => $user->id
            ]);
            return MessageFixer::success("Outlet has been added to favorite");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
