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
        Schema::create('outlet_favorites', function (Blueprint $table) {
            $table->ulid('id');
            $table->foreignIdFor(Outlet::class)->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreignIdFor(User::class)->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlet_favorites');
    }
};
