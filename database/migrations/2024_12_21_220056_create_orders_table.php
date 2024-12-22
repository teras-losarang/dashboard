<?php

use App\Models\Outlet;
use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->references("id")->on("users");
            $table->foreignIdFor(Outlet::class)->references("id")->on("outlets");
            $table->string('name', 100)->default('-');
            $table->string('phone', 15)->default('-');
            $table->string('address_user', 200)->default('-');
            $table->string('address_outlet', 200)->default('-');
            $table->unsignedInteger('total')->default(0);
            $table->string('created_by', 100)->default('-');
            $table->tinyInteger('status')->default(1)->comment('1: Order, 88: Cancel, 99: Done');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
