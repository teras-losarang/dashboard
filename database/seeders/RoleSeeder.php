<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [User::ROLE_TEXT_ADMIN, User::ROLE_TEXT_CUSTOMER];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('roles')->truncate();

        foreach ($roles as $key => $value) {
            Role::create([
                'name' => $value
            ]);
        }

        $user = User::find(1);
        $user->assignRole(Role::where('id', User::ROLE_ADMIN)->first());

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
