<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Http\Controllers\Controller;
use App\Models\Regency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class RegencyController extends Controller
{
    protected $regency;

    public function __construct()
    {
        $this->regency = new Regency();
    }

    public function index(Request $request)
    {
        $query = $this->regency->query();

        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countRegency = $query->count();
        $regencies = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countRegency > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_ERROR,
            message: $countRegency > 0 ? null : "Regency no available.",
            data: $countRegency > 0 ? $regencies->items() : null,
            paginate: ($regencies instanceof LengthAwarePaginator) && $countRegency > 0  ? [
                "current_page" => $regencies->currentPage(),
                "last_page" => $regencies->lastPage(),
                "total" => $regencies->total(),
                "from" => $regencies->firstItem(),
                "to" => $regencies->lastItem(),
            ] : null
        );
    }
}
