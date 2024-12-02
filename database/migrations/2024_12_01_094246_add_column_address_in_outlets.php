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
        Schema::table('outlets', function (Blueprint $table) {
            $table->string('address', 200)->default('-')->after("latitude");
            $table->string('created_by', 150)->default('-')->after("operational_hour");
            $table->json('images')->default('-')->after("created_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(["address", "created_by", "images"]);
        });
    }
};
