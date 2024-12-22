<?php

namespace App\Http\Controllers\API;

use App\Enums\StatusTypeEnum;
use App\Facades\MessageFixer;
use App\Filters\Order\Auth;
use App\Filters\Order\OrderId;
use App\Filters\Order\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Order\StoreRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $product, $order, $address, $outlet;

    public function __construct()
    {
        $this->product = new Product();
        $this->order = new Order();
        $this->address = new Address();
        $this->outlet = new Outlet();
    }

    public function index(Request $request)
    {
        $orders = app(Pipeline::class)
            ->send($this->order->query())
            ->through([
                Auth::class,
                OrderId::class,
                Status::class
            ])
            ->thenReturn()
            ->paginate($request->per_page);

        $orders->load([
            "items:id,order_id,price,quantity,subtotal,enable_variant,variants"
        ]);

        $orders->getCollection()->transform(function ($order) {
            $order->status_type = StatusTypeEnum::show($order->status);
            return $order;
        });

        if (($request->has("order_id") && $request->order_id > 0)) {
            if (count($orders->items()) < 1) {
                return MessageFixer::error("Data order not found.");
            }

            return MessageFixer::render(MessageFixer::DATA_OK, "Success", $orders[0]);
        }

        if (count($orders->items()) < 1) {
            return MessageFixer::error("Data order is empty.");
        }

        return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Success", data: $orders->items(), paginate: ($orders instanceof LengthAwarePaginator) && count($orders->items()) > 0  ? [
            "current_page" => $orders->currentPage(),
            "last_page" => $orders->lastPage(),
            "total" => $orders->total(),
            "from" => $orders->firstItem(),
            "to" => $orders->lastItem(),
        ] : null);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        $user = $request->user();
        $address = $this->address->find($request->address_id);
        $outlet = $this->outlet->find($request->outlet_id);

        $products = [];
        $totalOrder = 0;
        foreach ($request->products as $product) {
            $productDb = $this->product->find($product["product_id"]);
            $productDb->variants = json_encode([]);
            $subtotal = $productDb->price;
            if ($productDb->enable_variant && empty($product["variants"])) {
                return MessageFixer::error("Fill data correctly, entry variant!");
            } else {
                $productVariants = $productDb->variants()->whereIn("id", $product["variants"])->get(["name", "price", "id"]);
                foreach ($productVariants as $variant) {
                    $subtotal += (int) $variant->price;
                }

                $productDb->variants = json_encode($productVariants);
            }

            $totalOrder += (int) $subtotal * (int) $product["quantity"];
            array_push($products, [
                "product_id" => $productDb->id,
                "quantity" => (int) $product["quantity"],
                "price" => $productDb->price,
                "subtotal" => (int) $subtotal * (int) $product["quantity"],
                "enable_variant" => $productDb->enable_variant,
                "variants" => $productDb->variants
            ]);
        }

        try {
            $order = $this->order->create([
                "user_id" => $user->id,
                "outlet_id" => $outlet->id,
                "name" => $address->name,
                "phone" => $address->phone,
                "address_user" => "{$address->detail}, {$address->village->name}, {$address->district->name}, {$address->regency->name}, {$address->province->name}",
                "address_outlet" => $outlet->address,
                "total" => $totalOrder,
                "created_by" => $user->name
            ]);

            $order->items()->insert(array_map(function ($product) use ($order) {
                $product["order_id"] = $order->id;
                return $product;
            }, $products));

            DB::commit();
            return MessageFixer::success("Order has been successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            "status" => "required|integer|in:1,88,99",
            "order_id" => "required|integer"
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(MessageFixer::DATA_ERROR, "Fill data correctly!", $validator->errors());
        }

        $order = $this->order->find($request->order_id);
        if (!$order) {
            return MessageFixer::error("Data order not found.");
        }

        try {
            $order->status = $request->status;
            $order->save();

            DB::commit();
            return MessageFixer::success("Order has been successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
