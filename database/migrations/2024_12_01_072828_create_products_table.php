<?php

use App\Models\Outlet;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Outlet::class)->references("id")->on("outlets");
            $table->string("name", 150)->default("-");
            $table->string("slug", 150)->default("-");
            $table->unsignedInteger("price")->default(0);
            $table->tinyInteger("enable_variant")->default(0);
            $table->tinyInteger("status")->default(1);
            $table->json("images")->default("-");
            $table->string("created_by", 100)->default("-");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
