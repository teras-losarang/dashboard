<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Filters\Address\AddressId;
use App\Filters\Address\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Address\StoreRequest;
use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    protected $province, $regency, $district, $village, $address;

    public function __construct()
    {
        $this->province = new Province();
        $this->regency = new Regency();
        $this->district = new District();
        $this->village = new Village();
        $this->address = new Address();
    }

    public function index(Request $request)
    {
        $addresses = app(Pipeline::class)
            ->send($this->address->query())
            ->through([
                Auth::class,
                AddressId::class
            ])
            ->thenReturn()
            ->paginate($request->per_page);

        $addresses->load([
            "province",
            "regency:id,name",
            "district:id,name",
            "village:id,name"
        ]);

        if (($request->has("address_id") && $request->address_id > 0)) {
            if (count($addresses->items()) < 1) {
                return MessageFixer::error("Data category not found.");
            }

            return MessageFixer::render(MessageFixer::DATA_OK, "Success", $addresses[0]);
        }

        if (count($addresses->items()) < 1) {
            return MessageFixer::error("Data category is empty.");
        }

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $addresses->items(), paginate: ($addresses instanceof LengthAwarePaginator) && count($addresses->items()) > 0  ? [
            "current_page" => $addresses->currentPage(),
            "last_page" => $addresses->lastPage(),
            "total" => $addresses->total(),
            "from" => $addresses->firstItem(),
            "to" => $addresses->lastItem(),
        ] : null);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        $province = $this->province->with([
            'regencies.districts.villages' => function ($query) use ($request) {
                $query->where('villages.id', $request->village_id);
            }
        ])->find($request->province_id);
        if (!$province) {
            return MessageFixer::error("Province not found");
        }

        $regency = $province->regencies->firstWhere('id', $request->regency_id);
        if (!$regency) {
            return MessageFixer::error("Regency not found in this province [{$province->name}]");
        }

        $district = $regency->districts->firstWhere('id', $request->district_id);
        if (!$district) {
            return MessageFixer::error("District not found in this regency [{$regency->name}]");
        }

        $village = $district->villages->firstWhere('id', $request->village_id);
        if (!$village) {
            return MessageFixer::error("Village not found in this district [{$district->name}]");
        }

        $user = $request->user();

        try {
            if (
                $request->is_default &&
                $user->addresses->count() > 0
            ) {
                $user->addresses()->where('is_default', 1)->update([
                    'is_default' => 0
                ]);
            }

            $this->address->create([
                'name' => $request->name,
                'phone' => $request->phone,
                'detail' => $request->detail,
                'is_default' => $user->addresses->count() < 1 ? 1 : $request->is_default,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'district_id' => $district->id,
                'village_id' => $village->id,
                'user_id' => $user->id
            ]);

            DB::commit();
            return MessageFixer::success("Address has been created");
        } catch (\Throwable $th) {
            DB::rollback();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function update(StoreRequest $request)
    {
        DB::beginTransaction();

        $address = $this->address->find($request->address_id);
        if (!$address) {
            return MessageFixer::error(message: "Address not found");
        }

        $province = $this->province->with([
            'regencies.districts.villages' => function ($query) use ($request) {
                $query->where('villages.id', $request->village_id);
            }
        ])->find($request->province_id);
        if (!$province) {
            return MessageFixer::error("Province not found");
        }

        $regency = $province->regencies->firstWhere('id', $request->regency_id);
        if (!$regency) {
            return MessageFixer::error("Regency not found in this province [{$province->name}]");
        }

        $district = $regency->districts->firstWhere('id', $request->district_id);
        if (!$district) {
            return MessageFixer::error("District not found in this regency [{$regency->name}]");
        }

        $village = $district->villages->firstWhere('id', $request->village_id);
        if (!$village) {
            return MessageFixer::error("Village not found in this district [{$district->name}]");
        }

        $user = $request->user();

        try {
            if (
                $request->is_default
            ) {
                $user->addresses()->where('is_default', 1)->update([
                    'is_default' => 0
                ]);
            }

            $address->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'detail' => $request->detail,
                'is_default' => $request->is_default,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'district_id' => $district->id,
                'village_id' => $village->id,
            ]);

            DB::commit();
            return MessageFixer::success("Address has been updated");
        } catch (\Throwable $th) {
            DB::rollback();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();

        $address = $this->address->find($request->address_id);
        if (!$address) {
            return MessageFixer::error(message: "Address not found");
        }

        $user = $request->user();
        if ($user->addresses->count() == 1) {
            return MessageFixer::error(message: "Address can't remove. Address must have at least one address");
        }

        try {
            if ($address->is_default) {
                $tempAddress = $this->address->where(["user_id" => $user->id, "is_default" => 0])->first();
                $tempAddress->update([
                    'is_default' => 1
                ]);
            }

            $address->delete();

            DB::commit();
            return MessageFixer::success("Address has been deleted");
        } catch (\Throwable $th) {
            DB::rollback();
            return MessageFixer::error($th->getMessage());
        }
    }
}
