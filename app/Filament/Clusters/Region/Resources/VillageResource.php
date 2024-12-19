<?php

namespace App\Filament\Clusters\Region\Resources;

use App\Filament\Clusters\Region;
use App\Filament\Clusters\Region\Resources\VillageResource\Pages;
use App\Filament\Clusters\Region\Resources\VillageResource\RelationManagers;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VillageResource extends Resource
{
    protected static ?string $model = Village::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Region::class;

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("district_id")->label("District")->relationship("district", "name")->required()->preload()->searchable()->live(),
                TextInput::make("name")->required()->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->searchable()->sortable(),
                TextColumn::make("district.name")->sortable(),
                TextColumn::make("district.regency.name")->sortable(),
                TextColumn::make("district.regency.province.name")->sortable(),
            ])
            ->filters([
                SelectFilter::make("district_id")->label("District")
                    ->relationship('district', 'name')->preload()->searchable(),
                SelectFilter::make('regency_id')
                    ->label('Regency')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('district.regency', function (Builder $query) use ($data) {
                                $query->where('id', $data['value']);
                            });
                        }
                    })
                    ->options(function () {
                        return Regency::all()->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable(),
                SelectFilter::make('province_id')
                    ->label('Province')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('district.regency.province', function (Builder $query) use ($data) {
                                $query->where('id', $data['value']);
                            });
                        }
                    })
                    ->options(function () {
                        return Province::all()->pluck('name', 'id');
                    })
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVillages::route('/'),
            // 'create' => Pages\CreateVillage::route('/create'),
            // 'edit' => Pages\EditVillage::route('/{record}/edit'),
        ];
    }
}
