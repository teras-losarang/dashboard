<?php

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->references("id")->on("orders");
            $table->foreignIdFor(Product::class)->references("id")->on("products");
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->boolean('enable_variant')->default(0);
            $table->json('variants')->default('{}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
