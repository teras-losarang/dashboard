<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    protected $province;

    public function __construct()
    {
        $this->province = new Province();
    }

    public function index(Request $request)
    {
        $query = $this->province->query();

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countProvince = $query->count();
        $provinces = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countProvince > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_ERROR,
            message: $countProvince > 0 ? null : "Province no available.",
            data: $countProvince > 0 ? $provinces->items() : null,
            paginate: ($provinces instanceof LengthAwarePaginator) && $countProvince > 0  ? [
                "current_page" => $provinces->currentPage(),
                "last_page" => $provinces->lastPage(),
                "total" => $provinces->total(),
                "from" => $provinces->firstItem(),
                "to" => $provinces->lastItem(),
            ] : null
        );
    }
}
