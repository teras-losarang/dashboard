<?php

namespace App\Filament\Clusters\Region\Resources;

use App\Filament\Clusters\Region;
use App\Filament\Clusters\Region\Resources\RegencyResource\Pages;
use App\Filament\Clusters\Region\Resources\RegencyResource\RelationManagers;
use App\Models\Regency;
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

class RegencyResource extends Resource
{
    protected static ?string $model = Regency::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Region::class;

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make("province_id")->label("Province")->relationship("province", "name")->required()->preload()->searchable()->live(),
                TextInput::make("name")->required()->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->searchable()->sortable(),
                TextColumn::make("province.name")->sortable(),
            ])
            ->filters([
                SelectFilter::make('province_id')
                    ->label('Province')
                    ->relationship('province', 'name')
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
            'index' => Pages\ListRegencies::route('/'),
            // 'create' => Pages\CreateRegency::route('/create'),
            // 'edit' => Pages\EditRegency::route('/{record}/edit'),
        ];
    }
}
