<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make('passowrd');

        return $data;
    }

    protected function afterCreate(): void {
        DB::transaction(function () {
            $record = $this->record;
            $record->assignRole(Role::where('id', User::ROLE_CUSTOMER)->first());
        });
    }
}
