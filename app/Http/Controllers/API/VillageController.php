<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Models\Village;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    protected $village;

    public function __construct()
    {
        $this->village = new Village();
    }

    public function index(Request $request)
    {
        $query = $this->village->query();

        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countVillage = $query->count();
        $villages = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countVillage > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_ERROR,
            message: $countVillage > 0 ? null : "Village no available.",
            data: $countVillage > 0 ? $villages->items() : null,
            paginate: ($villages instanceof LengthAwarePaginator) && $countVillage > 0  ? [
                "current_page" => $villages->currentPage(),
                "last_page" => $villages->lastPage(),
                "total" => $villages->total(),
                "from" => $villages->firstItem(),
                "to" => $villages->lastItem(),
            ] : null
        );
    }
}
