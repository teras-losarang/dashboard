<?php

namespace App\Http\Controllers\API;

use App\Facades\MessageFixer;
use App\Filters\Product\OutletId;
use App\Filters\Product\ProductId;
use App\Filters\Product\Search;
use App\Filters\Product\Slug;
use App\Filters\Product\Tags;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Product\StoreRequest;
use App\Http\Requests\API\Product\UpdateRequest;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductHasCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $product, $outlet, $productCategory;

    public function __construct()
    {
        $this->product = new Product();
        $this->outlet = new Outlet();
        $this->productCategory = new ProductHasCategory();
    }

    public function index(Request $request)
    {
        $products = app(Pipeline::class)
            ->send($this->product->query()
                ->with('variants', function ($query) {
                    $query->where("status", 1);
                })->with('categories', function ($query) {
                    $query->where("status", 1);
                }))
            ->through([
                Search::class,
                Slug::class,
                ProductId::class,
                OutletId::class,
                Tags::class
            ])
            ->thenReturn()
            ->paginate($request->per_page);


        $products->load([
            'outlet:id,name,user_id',
            'outlet.user:id,name',
        ]);

        $products->getCollection()->transform(function ($product) {
            $images = json_decode($product->images, true);
            $imageConverts = [];
            foreach ($images as $image) {
                $imageConverts[] = asset(Storage::url($image));
            }

            $product->images = $imageConverts;
            $product->owner = [
                "outlet_id" => $product->outlet->id,
                "outlet_name" => $product->outlet->name,
                "owner_name" => $product->outlet->user->name,
            ];

            $product->tags = $product->categories->pluck("name")->toArray();

            unset($product->outlet, $product->categories, $product->created_by);

            return $product;
        });

        if (($request->has("product_id") && $request->product_id > 0) || ($request->has("slug") && $request->slug)) {
            if (count($products->items()) < 1) {
                return MessageFixer::error("Data product not found.");
            }

            return MessageFixer::render(MessageFixer::DATA_OK, "Success", $products[0]);
        }

        if (count($products->items()) < 1) {
            return MessageFixer::error("Data product is empty.");
        }

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $products->items(), paginate: ($products instanceof LengthAwarePaginator) && count($products->items()) > 0  ? [
            "current_page" => $products->currentPage(),
            "last_page" => $products->lastPage(),
            "total" => $products->total(),
            "from" => $products->firstItem(),
            "to" => $products->lastItem(),
        ] : null);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        $user = $request->user();
        $outlet = $user->outlet;

        if (!$outlet) {
            return MessageFixer::error("You are not outlet.");
        }

        $images = [];
        foreach ($request->file('images') as $image) {
            $images[] = $image->store('products');
        }

        try {
            $product = $this->product->create([
                "name" => $request->name,
                "price" => $request->price,
                "description" => $request->description,
                "enable_variant" => $request->enable_variant,
                "images" => json_encode($images),
                "outlet_id" => $outlet->id,
            ]);

            $product->categories()->sync($request->categories);

            if ($request->enable_variant == 1) {
                $variants = $request->variants;

                foreach ($variants as $variant) {
                    $product->variants()->create([
                        "name" => $variant["name"],
                        "price" => $variant["price"],
                        "status" => $variant["status"],
                    ]);
                }
            }

            DB::commit();
            return MessageFixer::success("Product has been added");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function update(UpdateRequest $request)
    {
        DB::beginTransaction();

        $product = $this->product->find($request->product_id);
        if (!$product) {
            return MessageFixer::error("Product not found.");
        }

        $images = [];
        if ($request->hasFile("images")) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('products');
            }
        } else {
            $images = json_decode($product->images, true);
        }

        try {
            $product->update([
                "name" => $request->name,
                "price" => $request->price,
                "description" => $request->description,
                "enable_variant" => $request->enable_variant,
                "images" => json_encode($images)
            ]);

            $product->categories()->sync($request->categories);

            if ($request->enable_variant == 1) {
                $product->variants()->delete();
                $variants = $request->variants;

                foreach ($variants as $variant) {
                    $product->variants()->create([
                        "name" => $variant["name"],
                        "price" => $variant["price"],
                        "status" => $variant["status"],
                    ]);
                }
            }

            DB::commit();
            return MessageFixer::success("Product has been updated");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            "product_id" => "required|integer"
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::DATA_ERROR, message: "Fill data correctly!", data: $validator->errors());
        }

        $product = $this->product->find($request->product_id);
        if (!$product) {
            return MessageFixer::error("Product not found.");
        }

        try {
            $product->forceDelete();

            DB::commit();
            return MessageFixer::success("Product has been deleted");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
