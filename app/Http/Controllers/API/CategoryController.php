<?php

namespace App\Http\Controllers\API;

use App\Enums\CategoryTypeEnum;
use App\Facades\MessageFixer;
use App\Filters\Category\CategoryId;
use App\Filters\Category\Search;
use App\Filters\Category\Slug;
use App\Filters\Category\Status;
use App\Filters\Category\Type;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryCollection;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function index(Request $request)
    {
        $categories = app(Pipeline::class)
            ->send($this->category->query())
            ->through([
                Search::class,
                Status::class,
                Type::class,
                CategoryId::class,
                Slug::class
            ])
            ->thenReturn()
            ->paginate($request->per_page);

        $categories->getCollection()->transform(function ($category) {
            unset($category->status, $category->deleted_at, $category->created_at, $category->updated_at);

            $category->type = CategoryTypeEnum::show($category->type);
            $category->image = asset(Storage::url($category->image));

            return $category;
        });

        if (($request->has("category_id") && $request->category_id > 0) || ($request->has("slug") && $request->slug)) {
            if (count($categories->items()) < 1) {
                return MessageFixer::error("Data category not found.");
            }

            return MessageFixer::render(MessageFixer::DATA_OK, "Success", $categories[0]);
        }

        if (count($categories->items()) < 1) {
            return MessageFixer::error("Data category is empty.");
        }

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $categories->items(), paginate: ($categories instanceof LengthAwarePaginator) && count($categories->items()) > 0  ? [
            "current_page" => $categories->currentPage(),
            "last_page" => $categories->lastPage(),
            "total" => $categories->total(),
            "from" => $categories->firstItem(),
            "to" => $categories->lastItem(),
        ] : null);
    }
}
