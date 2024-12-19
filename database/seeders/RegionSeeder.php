<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = storage_path("region.sql");

        if (!File::exists($filePath)) {
            return;
        }

        $sql = File::get($filePath);

        try {
            DB::unprepared($sql);
            return;
        } catch (\Exception $e) {
            return;
        }
    }
}
