<?php

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->references("id")->on("users")->cascadeOnDelete();
            $table->foreignIdFor(Province::class);
            $table->foreignIdFor(Regency::class);
            $table->foreignIdFor(District::class);
            $table->foreignIdFor(Village::class);
            $table->string('name', 100)->default("-");
            $table->string('phone', 15)->default("-");
            $table->string('detail', 200)->default("-")->nullable();
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
