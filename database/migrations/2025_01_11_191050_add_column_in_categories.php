<?php

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
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('sort')->default(0)->after('id');
            $table->boolean('enable_home')->default(false)->after('type');
            $table->integer('per_page')->default(0)->after('type');
            $table->enum('direction', [1, 2])->default(1)->after('type')->comment("1: Horizontal, 2: Vertical");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['sort', 'direction', 'enable_home', 'per_page']);
        });
    }
};
