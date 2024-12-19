<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    protected $district;

    public function __construct()
    {
        $this->district = new District();
    }

    public function index(Request $request)
    {
        $query = $this->district->query();

        if ($request->has('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countDistrict = $query->count();
        $districts = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countDistrict > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_ERROR,
            message: $countDistrict > 0 ? null : "District no available.",
            data: $countDistrict > 0 ? $districts->items() : null,
            paginate: ($districts instanceof LengthAwarePaginator) && $countDistrict > 0  ? [
                "current_page" => $districts->currentPage(),
                "last_page" => $districts->lastPage(),
                "total" => $districts->total(),
                "from" => $districts->firstItem(),
                "to" => $districts->lastItem(),
            ] : null
        );
    }
}
